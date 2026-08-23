"""Build a conservative, reviewable catalog manifest from curated Instagram posts."""

from __future__ import annotations

import argparse
import json
from pathlib import Path

import torch
from PIL import Image, UnidentifiedImageError
from transformers import CLIPModel, CLIPProcessor


IMAGE_EXTENSIONS = {".jpg", ".jpeg", ".png", ".webp"}
ALLOWED_CATEGORIES = {
    "dapur", "kamar-mandi", "kamar-tidur", "ruang-keluarga",
    "ruang-kerja", "ruang-makan",
}
PROMPTS = {
    "finished": "a finished professional interior design portfolio photograph with no people",
    "person": "a photograph where a person or worker is visible",
    "construction": "an unfinished interior construction site renovation work in progress",
    "promotion": "an Instagram promotional graphic with large text logo or advertisement",
    "drawing": "an architectural line drawing floor plan or 3D CAD SketchUp rendering",
}
UNSAFE = {"person", "construction", "promotion", "drawing"}


def images(post: Path) -> list[Path]:
    return sorted(path for path in post.iterdir() if path.is_file() and path.suffix.lower() in IMAGE_EXTENSIONS)


def classify(paths: list[Path], model: CLIPModel, processor: CLIPProcessor) -> list[tuple[Path, str, float]]:
    valid, opened = [], []
    for path in paths:
        try:
            with Image.open(path) as image:
                opened.append(image.convert("RGB").copy())
                valid.append(path)
        except (UnidentifiedImageError, OSError):
            pass
    if not opened:
        return []
    labels = list(PROMPTS)
    inputs = processor(text=[PROMPTS[label] for label in labels], images=opened, return_tensors="pt", padding=True)
    with torch.inference_mode():
        scores = model(**inputs).logits_per_image.softmax(dim=1)
    output = []
    for path, row in zip(valid, scores):
        confidence, index = row.max(dim=0)
        output.append((path, labels[index.item()], float(confidence.item())))
    return output


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", type=Path, required=True)
    parser.add_argument("--output", type=Path, required=True)
    parser.add_argument("--batch-size", type=int, default=48)
    args = parser.parse_args()

    root = args.root.resolve()
    posts = [
        post for category in (root / "curated").iterdir()
        if category.is_dir() and category.name in ALLOWED_CATEGORIES
        for post in category.iterdir()
        if post.is_dir() and len(images(post)) >= 2
    ]
    all_images = [image for post in posts for image in images(post)]
    print(f"Menilai {len(posts)} unggahan dan {len(all_images)} gambar...")
    model = CLIPModel.from_pretrained("openai/clip-vit-base-patch32")
    processor = CLIPProcessor.from_pretrained("openai/clip-vit-base-patch32")
    model.eval()

    results: dict[Path, tuple[str, float]] = {}
    for start in range(0, len(all_images), args.batch_size):
        for path, label, confidence in classify(all_images[start:start + args.batch_size], model, processor):
            results[path] = (label, confidence)
        done = min(start + args.batch_size, len(all_images))
        if done % 480 == 0 or done == len(all_images):
            print(f"Memeriksa {done}/{len(all_images)} gambar")

    accepted, rejected = [], []
    for post in posts:
        post_images = images(post)
        labels = [results.get(image, ("drawing", 1.0))[0] for image in post_images]
        if any(label in UNSAFE for label in labels):
            rejected.append({"post": str(post.relative_to(root)), "labels": labels})
            continue
        accepted.append({
            "category": post.parent.name,
            "post": post.name,
            "images": [str(image.relative_to(root.parent.parent).as_posix()) for image in post_images],
        })

    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps({"accepted": accepted, "rejected": rejected}, indent=2), encoding="utf-8")
    print(f"Lolos: {len(accepted)} unggahan. Ditolak: {len(rejected)} unggahan.")
    print(f"Manifest: {args.output}")


if __name__ == "__main__":
    main()

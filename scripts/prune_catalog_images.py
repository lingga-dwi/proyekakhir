"""Remove non-portfolio catalog assets while retaining a reversible quarantine."""

from __future__ import annotations

import argparse
import csv
import shutil
from pathlib import Path

import torch
from PIL import Image, UnidentifiedImageError
from transformers import CLIPModel, CLIPProcessor


IMAGE_EXTENSIONS = {".jpg", ".jpeg", ".png", ".webp"}
PROMPTS = {
    "portfolio": "a finished photorealistic interior or architecture portfolio photograph",
    "line-drawing": "a black and white architectural line drawing, floor plan, or technical sketch",
    "cad-3d": "a simple non-photorealistic 3D CAD architecture model or SketchUp screenshot",
    "promotional": "a promotional social media graphic with logo, headline text, or advertisement",
}
REMOVABLE = {"line-drawing", "cad-3d", "promotional"}


def image_files(directory: Path) -> list[Path]:
    return [path for path in directory.iterdir() if path.is_file() and path.suffix.lower() in IMAGE_EXTENSIONS]


def classify_batch(paths: list[Path], model: CLIPModel, processor: CLIPProcessor) -> list[tuple[Path, str, float]]:
    valid_paths: list[Path] = []
    images: list[Image.Image] = []
    for path in paths:
        try:
            with Image.open(path) as image:
                images.append(image.convert("RGB").copy())
                valid_paths.append(path)
        except (UnidentifiedImageError, OSError):
            continue

    if not images:
        return []
    labels = list(PROMPTS)
    inputs = processor(text=[PROMPTS[label] for label in labels], images=images, return_tensors="pt", padding=True)
    with torch.inference_mode():
        probabilities = model(**inputs).logits_per_image.softmax(dim=1)
    results: list[tuple[Path, str, float]] = []
    for path, row in zip(valid_paths, probabilities):
        score, index = row.max(dim=0)
        results.append((path, labels[index.item()], float(score.item())))
    return results


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", type=Path, required=True)
    parser.add_argument("--apply", action="store_true")
    parser.add_argument("--batch-size", type=int, default=96)
    parser.add_argument("--confidence", type=float, default=0.52)
    args = parser.parse_args()

    root = args.root.resolve()
    curated = root / "curated"
    quarantine = root / "_dihapus"
    if not curated.is_dir():
        raise SystemExit(f"Folder tidak ditemukan: {curated}")

    report: list[dict[str, object]] = []
    single_posts = [folder for folder in curated.glob("*/*") if folder.is_dir() and len(image_files(folder)) <= 1]
    for folder in single_posts:
        category = folder.parent.name
        destination = quarantine / "unggahan-satu-foto" / category / folder.name
        report.append({"file": str(folder.relative_to(root)), "reason": "unggahan-satu-foto", "confidence": "-"})
        if args.apply:
            destination.parent.mkdir(parents=True, exist_ok=True)
            if not destination.exists():
                shutil.move(str(folder), str(destination))

    remaining_images = [
        image
        for category in curated.iterdir()
        if category.is_dir()
        for post in category.iterdir()
        if post.is_dir()
        for image in image_files(post)
    ]

    print("Memuat penyaring visual...")
    model = CLIPModel.from_pretrained("openai/clip-vit-base-patch32")
    processor = CLIPProcessor.from_pretrained("openai/clip-vit-base-patch32")
    model.eval()

    moved_files = 0
    for start in range(0, len(remaining_images), args.batch_size):
        for image, classification, confidence in classify_batch(remaining_images[start : start + args.batch_size], model, processor):
            if classification not in REMOVABLE or confidence < args.confidence:
                continue
            category = image.parent.parent.name
            post = image.parent.name
            destination = quarantine / classification / category / post / image.name
            report.append(
                {
                    "file": str(image.relative_to(root)),
                    "reason": classification,
                    "confidence": round(confidence, 4),
                }
            )
            if args.apply:
                destination.parent.mkdir(parents=True, exist_ok=True)
                if not destination.exists():
                    shutil.move(str(image), str(destination))
                    moved_files += 1

        completed = min(start + args.batch_size, len(remaining_images))
        if completed % 480 == 0 or completed == len(remaining_images):
            print(f"Menyaring {completed}/{len(remaining_images)} gambar")

    if args.apply:
        for category in curated.iterdir():
            if not category.is_dir():
                continue
            for post in list(category.iterdir()):
                if post.is_dir() and not any(post.iterdir()):
                    post.rmdir()

    report_path = root / "catalog-prune-report.csv"
    with report_path.open("w", newline="", encoding="utf-8") as report_file:
        writer = csv.DictWriter(report_file, fieldnames=["file", "reason", "confidence"])
        writer.writeheader()
        writer.writerows(report)

    print(f"Unggahan satu foto: {len(single_posts)}")
    print(f"Gambar sketsa/CAD/promosi: {moved_files if args.apply else sum(1 for row in report if row['reason'] != 'unggahan-satu-foto')}")
    print(f"Laporan: {report_path}")


if __name__ == "__main__":
    main()

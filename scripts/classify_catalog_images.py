"""Classify downloaded Instagram catalog images into safe local folders.

Each source directory is treated as one Instagram post.  Relevant design
images are moved into ``curated/<category>/<post-id>``.  Promotional graphics,
portraits, and uncertain files are preserved in ``_dihapus`` or
``perlu-ditinjau`` so no source asset is permanently deleted by automation.
"""

from __future__ import annotations

import argparse
import csv
import shutil
from collections import Counter, defaultdict
from pathlib import Path

import torch
from PIL import Image, UnidentifiedImageError
from transformers import CLIPModel, CLIPProcessor


IMAGE_EXTENSIONS = {".jpg", ".jpeg", ".png", ".webp"}

# The labels deliberately favour broad room types.  Specific styles and
# catalog titles remain an admin decision after the visual review.
PROMPTS = {
    "kamar-tidur": "a professional interior design photograph of a bedroom",
    "ruang-keluarga": "a professional interior design photograph of a living room",
    "dapur": "a professional interior design photograph of a kitchen",
    "ruang-makan": "a professional interior design photograph of a dining room",
    "kamar-mandi": "a professional interior design photograph of a bathroom",
    "ruang-kerja": "a professional interior design photograph of a home office workspace",
    "ruang-usaha": "a professional photograph of a cafe shop restaurant office or commercial interior",
    "eksterior": "a professional photograph of a house exterior terrace balcony garden or facade",
    "furnitur-detail": "a professional photograph of interior furniture or an interior design detail",
    "foto-orang": "a portrait or photograph where a person is the main subject",
    "promosi": "an Instagram promotional graphic with text logo price announcement or advertisement",
    "perlu-ditinjau": "an unrelated image that is not an interior design portfolio photograph",
}

REJECTED_CATEGORIES = {"foto-orang", "promosi"}


def is_source_post(directory: Path, root: Path) -> bool:
    """Ignore output folders and keep only original Instagram post folders."""
    return directory.parent == root and directory.name not in {
        "curated",
        "_dihapus",
        "perlu-ditinjau",
        "kantor",
        "rumah",
        "usaha",
    }


def classify_images_batch(
    image_paths: list[Path], model: CLIPModel, processor: CLIPProcessor
) -> list[tuple[Path, str, float]]:
    valid_paths: list[Path] = []
    images: list[Image.Image] = []
    results: list[tuple[Path, str, float]] = []
    for image_path in image_paths:
        try:
            with Image.open(image_path) as image:
                images.append(image.convert("RGB").copy())
                valid_paths.append(image_path)
        except (UnidentifiedImageError, OSError):
            results.append((image_path, "perlu-ditinjau", 0.0))

    if not images:
        return results
    labels = list(PROMPTS)
    inputs = processor(
        text=[PROMPTS[label] for label in labels],
        images=images,
        return_tensors="pt",
        padding=True,
    )
    with torch.inference_mode():
        probabilities = model(**inputs).logits_per_image.softmax(dim=1)
    for image_path, row in zip(valid_paths, probabilities):
        score, index = row.max(dim=0)
        results.append((image_path, labels[index.item()], float(score.item())))
    return results


def destination_for(category: str, root: Path, post_name: str) -> Path:
    if category in REJECTED_CATEGORIES:
        return root / "_dihapus" / category / post_name
    if category == "perlu-ditinjau":
        return root / "perlu-ditinjau" / post_name
    return root / "curated" / category / post_name


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", type=Path, required=True)
    parser.add_argument("--apply", action="store_true", help="Move files instead of creating a report only.")
    parser.add_argument("--confidence", type=float, default=0.34)
    parser.add_argument("--limit", type=int, help="Limit source posts, useful for a safe test run.")
    parser.add_argument("--batch-size", type=int, default=24)
    args = parser.parse_args()

    root = args.root.resolve()
    if not root.is_dir():
        raise SystemExit(f"Folder tidak ditemukan: {root}")

    print("Memuat model klasifikasi visual...")
    model = CLIPModel.from_pretrained("openai/clip-vit-base-patch32")
    processor = CLIPProcessor.from_pretrained("openai/clip-vit-base-patch32")
    model.eval()

    post_directories = [directory for directory in root.iterdir() if directory.is_dir() and is_source_post(directory, root)]
    if args.limit:
        post_directories = post_directories[: args.limit]
    post_images = {
        post_dir: [path for path in post_dir.iterdir() if path.is_file() and path.suffix.lower() in IMAGE_EXTENSIONS]
        for post_dir in post_directories
    }
    all_images = [image for images in post_images.values() for image in images]
    classifications_by_path: dict[Path, tuple[str, float]] = {}
    for start in range(0, len(all_images), args.batch_size):
        batch = classify_images_batch(all_images[start : start + args.batch_size], model, processor)
        for image, category, confidence in batch:
            classifications_by_path[image] = (
                "perlu-ditinjau" if confidence < args.confidence else category,
                confidence,
            )
        completed = min(start + args.batch_size, len(all_images))
        if completed % 240 == 0 or completed == len(all_images):
            print(f"Klasifikasi visual {completed}/{len(all_images)} gambar")

    report_rows: list[dict[str, object]] = []
    counts: Counter[str] = Counter()
    for number, post_dir in enumerate(post_directories, start=1):
        classifications = [
            (image, *classifications_by_path[image])
            for image in post_images[post_dir]
        ]

        # A carousel may contain a text cover and several design photos. Keep
        # relevant photos under the dominant room category for that post.
        relevant = [item for item in classifications if item[1] not in REJECTED_CATEGORIES | {"perlu-ditinjau"}]
        post_category = Counter(item[1] for item in relevant).most_common(1)
        dominant = post_category[0][0] if post_category else None

        for image, category, confidence in classifications:
            target_category = dominant if dominant and category not in REJECTED_CATEGORIES | {"perlu-ditinjau"} else category
            destination = destination_for(target_category, root, post_dir.name) / image.name
            report_rows.append(
                {
                    "post": post_dir.name,
                    "file": image.name,
                    "classification": category,
                    "destination_category": target_category,
                    "confidence": round(confidence, 4),
                    "action": "moved" if args.apply else "planned",
                }
            )
            counts[target_category] += 1

            if args.apply:
                destination.parent.mkdir(parents=True, exist_ok=True)
                if not destination.exists():
                    shutil.move(str(image), str(destination))

        if args.apply and not any(post_dir.iterdir()):
            post_dir.rmdir()

        if number % 200 == 0 or number == len(post_directories):
            print(f"Menyusun {number}/{len(post_directories)} unggahan")

    report_path = root / "catalog-image-classification.csv"
    with report_path.open("w", newline="", encoding="utf-8") as report_file:
        writer = csv.DictWriter(report_file, fieldnames=report_rows[0].keys() if report_rows else [])
        if report_rows:
            writer.writeheader()
            writer.writerows(report_rows)

    print("\nRingkasan:")
    for category, count in sorted(counts.items()):
        print(f"- {category}: {count}")
    print(f"Laporan: {report_path}")
    print("Mode:", "pemindahan aktif" if args.apply else "pratinjau")


if __name__ == "__main__":
    main()

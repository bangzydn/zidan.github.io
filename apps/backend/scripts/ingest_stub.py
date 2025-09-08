import argparse
from pathlib import Path

KNOWLEDGE_DIR = Path(__file__).resolve().parents[2] / "knowledge"
STORE_FILE = KNOWLEDGE_DIR / "seed.txt"


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--file", type=str, required=True, help="Markdown or txt file to ingest")
    args = parser.parse_args()

    src = Path(args.file)
    if not src.exists():
        raise FileNotFoundError(src)

    text = src.read_text(encoding="utf-8")
    lines = [line.strip() for line in text.splitlines() if line.strip()]

    KNOWLEDGE_DIR.mkdir(parents=True, exist_ok=True)
    with STORE_FILE.open("a", encoding="utf-8") as f:
        for line in lines:
            f.write(line + "\n")

    print(f"Ingested {len(lines)} lines into {STORE_FILE}")


if __name__ == "__main__":
    main()
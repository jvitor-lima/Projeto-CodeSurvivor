#!/usr/bin/env python3
"""Analise simples de respostas da avaliacao do Code Survivor.

Uso exemplo:
    python scripts/analisar-avaliacao.py respostas.csv --sus-prefix SUS --geral-col "Nota geral"

O script usa apenas bibliotecas padrao para calcular estatisticas. Se matplotlib
estiver instalado, gera um boxplot da nota geral em docs/avaliacao-code-survivor/graficos/.
"""

from __future__ import annotations

import argparse
import csv
import json
import math
from collections import Counter
from pathlib import Path
from statistics import StatisticsError, mean, mode, stdev, variance


def parse_number(value: str) -> float | None:
    if value is None:
        return None

    cleaned = str(value).strip().replace(",", ".")
    if cleaned == "":
        return None

    try:
        return float(cleaned)
    except ValueError:
        return None


def read_csv(path: Path) -> list[dict[str, str]]:
    with path.open("r", encoding="utf-8-sig", newline="") as handle:
        return list(csv.DictReader(handle))


def numeric_columns(rows: list[dict[str, str]]) -> dict[str, list[float]]:
    columns: dict[str, list[float]] = {}
    if not rows:
        return columns

    for name in rows[0].keys():
        values = [parse_number(row.get(name, "")) for row in rows]
        numbers = [value for value in values if value is not None]
        if numbers:
            columns[name] = numbers

    return columns


def safe_mode(values: list[float]) -> float | list[float] | None:
    if not values:
        return None

    counts = Counter(values)
    highest = max(counts.values())
    modes = sorted(value for value, count in counts.items() if count == highest)

    if len(modes) == 1:
        return modes[0]

    return modes


def describe(values: list[float]) -> dict[str, float | list[float] | None]:
    if not values:
        return {
            "n": 0,
            "media": None,
            "moda": None,
            "variancia_amostral": None,
            "desvio_padrao_amostral": None,
        }

    result: dict[str, float | list[float] | None] = {
        "n": len(values),
        "media": mean(values),
        "moda": safe_mode(values),
        "min": min(values),
        "max": max(values),
    }

    if len(values) >= 2:
        result["variancia_amostral"] = variance(values)
        result["desvio_padrao_amostral"] = stdev(values)
    else:
        result["variancia_amostral"] = None
        result["desvio_padrao_amostral"] = None

    return result


def calculate_sus(rows: list[dict[str, str]], prefix: str) -> list[float]:
    scores: list[float] = []
    required = [f"{prefix}{index}" for index in range(1, 11)]

    for row in rows:
        responses = [parse_number(row.get(column, "")) for column in required]
        if any(value is None for value in responses):
            continue

        adjusted = []
        for index, value in enumerate(responses, start=1):
            assert value is not None
            if index % 2 == 1:
                adjusted.append(value - 1)
            else:
                adjusted.append(5 - value)

        scores.append(sum(adjusted) * 2.5)

    return scores


def write_boxplot(values: list[float], output: Path) -> str | None:
    if not values:
        return None

    try:
        import matplotlib.pyplot as plt
    except ImportError:
        return None

    output.parent.mkdir(parents=True, exist_ok=True)
    plt.figure(figsize=(6, 4))
    plt.boxplot(values, vert=True)
    plt.title("Boxplot da experiência geral")
    plt.ylabel("Nota de 0 a 10")
    plt.ylim(0, 10)
    plt.tight_layout()
    plt.savefig(output, dpi=160)
    plt.close()
    return str(output)


def main() -> None:
    parser = argparse.ArgumentParser(description="Analisa CSV de avaliacao do Code Survivor.")
    parser.add_argument("csv_path", type=Path, help="CSV exportado do Google Forms.")
    parser.add_argument("--sus-prefix", default=None, help="Prefixo das colunas SUS, exemplo: SUS para SUS1..SUS10.")
    parser.add_argument("--geral-col", default=None, help="Nome da coluna da nota geral de 0 a 10.")
    parser.add_argument(
        "--out-dir",
        type=Path,
        default=Path("docs/avaliacao-code-survivor/graficos"),
        help="Pasta de saida para graficos.",
    )
    args = parser.parse_args()

    rows = read_csv(args.csv_path)
    columns = numeric_columns(rows)

    summary = {
        "arquivo": str(args.csv_path),
        "participantes": len(rows),
        "colunas_numericas": {name: describe(values) for name, values in columns.items()},
    }

    if args.sus_prefix:
        sus_scores = calculate_sus(rows, args.sus_prefix)
        summary["sus"] = describe(sus_scores)
        summary["sus_scores"] = sus_scores

    if args.geral_col:
        geral_values = columns.get(args.geral_col, [])
        summary["experiencia_geral"] = describe(geral_values)
        boxplot_path = write_boxplot(geral_values, args.out_dir / "boxplot-experiencia-geral.png")
        summary["boxplot_experiencia_geral"] = boxplot_path or "Nao gerado; instale matplotlib para gerar graficos."

    print(json.dumps(summary, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()


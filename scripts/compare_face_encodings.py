#!/usr/bin/env python3
"""
Script para comparar dois encodings faciais
Uso: python3 compare_face_encodings.py <encoding1> <encoding2>
"""

import sys
import numpy as np
import json

def compare_face_encodings(encoding1_json, encoding2_json):
    """
    Compara dois encodings faciais e retorna um score de similaridade
    """
    try:
        # Converter JSON strings para arrays numpy
        encoding1 = np.array(json.loads(encoding1_json), dtype=np.float32)
        encoding2 = np.array(json.loads(encoding2_json), dtype=np.float32)

        # Verificar se os encodings têm o mesmo tamanho
        if encoding1.shape != encoding2.shape:
            print("Encodings têm tamanhos diferentes", file=sys.stderr)
            return None

        # Calcular similaridade usando correlação
        # Valores próximos de 1 indicam alta similaridade
        correlation = np.corrcoef(encoding1, encoding2)[0, 1]

        # Converter para score de 0-1 (0 = diferente, 1 = idêntico)
        similarity_score = max(0, correlation)

        return similarity_score

    except Exception as e:
        print(f"Erro ao comparar encodings: {str(e)}", file=sys.stderr)
        return None

def main():
    if len(sys.argv) != 3:
        print("Uso: python3 compare_face_encodings.py <encoding1> <encoding2>", file=sys.stderr)
        sys.exit(1)

    encoding1_json = sys.argv[1]
    encoding2_json = sys.argv[2]

    # Comparar encodings
    similarity = compare_face_encodings(encoding1_json, encoding2_json)

    if similarity is not None:
        print(similarity)
    else:
        sys.exit(1)

if __name__ == "__main__":
    main()

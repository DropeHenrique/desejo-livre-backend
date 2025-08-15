#!/usr/bin/env python3
"""
Script para extrair encoding facial de uma imagem ou PDF
Uso: python3 extract_face_encoding.py <caminho_da_imagem_ou_pdf>
"""

import sys
import numpy as np
import json
from PIL import Image
import cv2
import fitz  # PyMuPDF para PDFs
import tempfile
import os

def convert_pdf_to_image(pdf_path):
    """
    Converte a primeira página de um PDF para imagem
    """
    try:
        # Abrir o PDF
        pdf_document = fitz.open(pdf_path)

        if len(pdf_document) == 0:
            print("PDF vazio", file=sys.stderr)
            return None

        # Pegar a primeira página
        page = pdf_document[0]

        # Converter para imagem com alta resolução
        mat = fitz.Matrix(2.0, 2.0)  # 2x zoom para melhor qualidade
        pix = page.get_pixmap(matrix=mat)

        # Salvar temporariamente
        temp_path = tempfile.mktemp(suffix='.png')
        pix.save(temp_path)

        pdf_document.close()
        return temp_path

    except Exception as e:
        print(f"Erro ao converter PDF: {str(e)}", file=sys.stderr)
        return None

def extract_face_encoding(image_path):
    """
    Extrai o encoding facial de uma imagem
    """
    try:
        # Verificar se é PDF
        if image_path.lower().endswith('.pdf'):
            # Converter PDF para imagem
            temp_image_path = convert_pdf_to_image(image_path)
            if not temp_image_path:
                return None

            try:
                # Processar a imagem convertida
                encoding = extract_face_encoding_from_image(temp_image_path)
                return encoding
            finally:
                # Limpar arquivo temporário
                if os.path.exists(temp_image_path):
                    os.remove(temp_image_path)
        else:
            # Processar imagem diretamente
            return extract_face_encoding_from_image(image_path)

    except Exception as e:
        print(f"Erro ao processar arquivo: {str(e)}", file=sys.stderr)
        return None

def extract_face_encoding_from_image(image_path):
    """
    Extrai o encoding facial de uma imagem usando OpenCV
    """
    try:
        # Carregar imagem
        image = cv2.imread(image_path)
        if image is None:
            print("Não foi possível carregar a imagem", file=sys.stderr)
            return None

        # Converter para escala de cinza
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

        # Carregar classificador de faces do OpenCV
        face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

        # Detectar faces
        faces = face_cascade.detectMultiScale(gray, 1.1, 4)

        if len(faces) == 0:
            print("Nenhum rosto detectado na imagem", file=sys.stderr)
            return None

        # Se múltiplas faces, usar a primeira
        if len(faces) > 1:
            print(f"Detectadas {len(faces)} faces, usando a primeira", file=sys.stderr)

        # Pegar a primeira face detectada
        (x, y, w, h) = faces[0]

        # Extrair região da face
        face_roi = gray[y:y+h, x:x+w]

        # Redimensionar para tamanho padrão
        face_roi = cv2.resize(face_roi, (128, 128))

        # Normalizar e converter para lista
        face_roi = face_roi.astype(np.float32) / 255.0
        encoding_list = face_roi.flatten().tolist()

        # Retornar como JSON string
        return json.dumps(encoding_list)

    except Exception as e:
        print(f"Erro ao processar imagem: {str(e)}", file=sys.stderr)
        return None

def main():
    if len(sys.argv) != 2:
        print("Uso: python3 extract_face_encoding.py <caminho_da_imagem_ou_pdf>", file=sys.stderr)
        sys.exit(1)

    file_path = sys.argv[1]

    # Verificar se arquivo existe
    try:
        with open(file_path, 'rb') as f:
            pass
    except FileNotFoundError:
        print(f"Arquivo não encontrado: {file_path}", file=sys.stderr)
        sys.exit(1)

    # Extrair encoding
    encoding = extract_face_encoding(file_path)

    if encoding:
        print(encoding)
    else:
        sys.exit(1)

if __name__ == "__main__":
    main()

#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
AI Model Fetcher - Absensi Siswa & PKL SMK NU Wargabinangun
Mengambil dan menampilkan daftar model AI aktif secara dinamis dari API Provider
Mendukung:
  1. Groq (api.groq.com/openai/v1/models) - Llama 3.3, Llama 3.1, Gemma 2, Mixtral
  2. xAI Grok (api.x.ai/v1/models) - Grok 2, Grok Beta
  3. Google Gemini (generativelanguage.googleapis.com/v1beta/models)
"""

import sys
import json
import argparse
import urllib.request
import urllib.error
import ssl

def detect_provider_from_key(api_key: str) -> str:
    key = api_key.strip()
    if key.startswith("gsk_"):
        return "groq"
    elif key.startswith("xai-"):
        return "grok"
    elif key.startswith("AIza"):
        return "gemini"
    elif key.startswith("sk-"):
        return "groq"
    return "groq"

def fetch_groq_models(api_key: str):
    url = "https://api.groq.com/openai/v1/models"
    req = urllib.request.Request(url, headers={
        "Authorization": f"Bearer {api_key.strip()}",
        "Content-Type": "application/json",
        "User-Agent": "SMK-NU-Absensi-AI/1.0"
    })
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE

    with urllib.request.urlopen(req, context=ctx, timeout=15) as resp:
        data = json.loads(resp.read().decode("utf-8"))
        raw_models = data.get("data", [])
        
        # Filter model yang relevan untuk chat / text
        chat_models = []
        for m in raw_models:
            mid = m.get("id", "")
            # Prioritaskan model populer Groq
            is_active = m.get("active", True)
            chat_models.append({
                "id": mid,
                "name": mid,
                "owned_by": m.get("owned_by", "groq"),
                "context_window": m.get("context_window", "-"),
                "provider": "groq",
                "is_recommended": "llama-3.3-70b-versatile" in mid or "llama-3.1-8b-instant" in mid
            })
        
        # Urutkan agar model rekomendasi muncul paling atas
        chat_models.sort(key=lambda x: (not x["is_recommended"], x["id"]))
        return chat_models

def fetch_grok_models(api_key: str):
    url = "https://api.x.ai/v1/models"
    req = urllib.request.Request(url, headers={
        "Authorization": f"Bearer {api_key.strip()}",
        "Content-Type": "application/json",
        "User-Agent": "SMK-NU-Absensi-AI/1.0"
    })
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE

    with urllib.request.urlopen(req, context=ctx, timeout=15) as resp:
        data = json.loads(resp.read().decode("utf-8"))
        raw_models = data.get("data", [])
        models = []
        for m in raw_models:
            mid = m.get("id", "")
            models.append({
                "id": mid,
                "name": mid,
                "owned_by": m.get("owned_by", "xai"),
                "context_window": m.get("context_window", "-"),
                "provider": "grok",
                "is_recommended": "grok-2" in mid or "grok-beta" in mid
            })
        models.sort(key=lambda x: (not x["is_recommended"], x["id"]))
        return models

def fetch_gemini_models(api_key: str):
    url = f"https://generativelanguage.googleapis.com/v1beta/models?key={api_key.strip()}"
    req = urllib.request.Request(url, headers={
        "Content-Type": "application/json",
        "User-Agent": "SMK-NU-Absensi-AI/1.0"
    })
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE

    with urllib.request.urlopen(req, context=ctx, timeout=15) as resp:
        data = json.loads(resp.read().decode("utf-8"))
        raw_models = data.get("models", [])
        models = []
        for m in raw_models:
            mid = m.get("name", "").replace("models/", "")
            methods = m.get("supportedGenerationMethods", [])
            if "generateContent" in methods:
                models.append({
                    "id": mid,
                    "name": m.get("displayName", mid),
                    "owned_by": "google",
                    "context_window": m.get("inputTokenLimit", "-"),
                    "provider": "gemini",
                    "is_recommended": mid in ["gemini-2.5-flash", "gemini-2.0-flash", "gemini-1.5-flash"]
                })
        models.sort(key=lambda x: (not x["is_recommended"], x["id"]))
        return models

def main():
    parser = argparse.ArgumentParser(description="Fetch and list available AI models for SMK NU Absensi Chatbot")
    parser.add_argument("--provider", default="auto", choices=["auto", "groq", "grok", "gemini"], help="AI Provider")
    parser.add_argument("--api-key", default="", help="API Key Provider")
    parser.add_argument("--json", action="store_true", help="Output pure JSON format")

    args = parser.parse_args()

    api_key = args.api_key.strip()
    provider = args.provider.lower()

    if not api_key:
        # Default fallback models jika tidak ada API key
        default_groq = [
            {"id": "llama-3.3-70b-versatile", "name": "Llama 3.3 70B Versatile", "provider": "groq", "is_recommended": True, "context_window": "128k"},
            {"id": "llama-3.1-8b-instant", "name": "Llama 3.1 8B Instant (Ultra Cepat)", "provider": "groq", "is_recommended": True, "context_window": "128k"},
            {"id": "gemma2-9b-it", "name": "Gemma 2 9B IT", "provider": "groq", "is_recommended": False, "context_window": "8k"},
            {"id": "mixtral-8x7b-32768", "name": "Mixtral 8x7B 32k", "provider": "groq", "is_recommended": False, "context_window": "32k"}
        ]
        default_grok = [
            {"id": "grok-2-latest", "name": "xAI Grok 2 (Latest)", "provider": "grok", "is_recommended": True, "context_window": "128k"},
            {"id": "grok-beta", "name": "xAI Grok Beta", "provider": "grok", "is_recommended": True, "context_window": "128k"}
        ]
        default_gemini = [
            {"id": "gemini-2.5-flash", "name": "Gemini 2.5 Flash", "provider": "gemini", "is_recommended": True, "context_window": "1M"},
            {"id": "gemini-2.0-flash", "name": "Gemini 2.0 Flash", "provider": "gemini", "is_recommended": True, "context_window": "1M"},
            {"id": "gemini-1.5-flash", "name": "Gemini 1.5 Flash", "provider": "gemini", "is_recommended": False, "context_window": "1M"}
        ]

        res = {
            "success": True,
            "provider": "default",
            "message": "Daftar model standar (API Key belum disertakan)",
            "models": default_groq + default_grok + default_gemini
        }
        if args.json or True:
            print(json.dumps(res, indent=2))
        return

    if provider == "auto":
        provider = detect_provider_from_key(api_key)

    try:
        if provider == "groq":
            models = fetch_groq_models(api_key)
        elif provider == "grok":
            models = fetch_grok_models(api_key)
        elif provider == "gemini":
            models = fetch_gemini_models(api_key)
        else:
            models = fetch_groq_models(api_key)

        res = {
            "success": True,
            "provider": provider,
            "total_models": len(models),
            "message": f"Berhasil mengambil {len(models)} model aktif dari {provider.upper()}",
            "models": models
        }
        print(json.dumps(res, indent=2))
    except urllib.error.HTTPError as e:
        err_body = e.read().decode("utf-8", errors="ignore")
        res = {
            "success": False,
            "provider": provider,
            "error_code": e.code,
            "message": f"Gagal mengambil model dari {provider.upper()} (HTTP {e.code}): Kunci API tidak valid atau izin ditolak.",
            "raw_error": err_body[:300]
        }
        print(json.dumps(res, indent=2))
    except Exception as e:
        res = {
            "success": False,
            "provider": provider,
            "message": f"Terjadi kesalahan: {str(e)}"
        }
        print(json.dumps(res, indent=2))

if __name__ == "__main__":
    main()

#!/usr/bin/env python3
"""
HablaIA - 50 prompt tests against LLM provider
Usage: python3 scripts/groq-test-50.py [base_url] [model_label]
Default: http://localhost:8080 groq-llama-70b
"""

import json
import sys
import time
import urllib.request
import urllib.error
from datetime import datetime

BASE_URL = sys.argv[1] if len(sys.argv) > 1 else "http://localhost:8080"
MODEL_LABEL = sys.argv[2] if len(sys.argv) > 2 else "groq-llama-70b"
ENDPOINT = f"{BASE_URL}/api/phrases/generate"
DELAY = 6.0  # seconds between requests (8K TPM for gpt-oss models)

# Pictogram UUIDs
P = {
    "yo": "b8da0502-aebf-4d7f-a1dc-66b21dbb3741",
    "tu": "329dd61f-1f79-456e-a666-2ad4f47d56ad",
    "ella": "be4238e8-72de-40eb-a184-709941715cae",
    "nosotros": "a9c74823-7fce-4a73-a137-ee94ad4634fb",
    "mama": "08ee54f9-f4b6-45e2-961f-52a62d000e86",
    "papa": "5a801db7-f1ab-463e-a608-db1d81610175",
    "abuela": "b2f6871b-92b4-4fea-8075-8c274bd8703e",
    "abuelo": "9d44916b-2ae3-4595-a275-144fb8ff83c3",
    "hermano": "2e0e9278-040a-4a2b-bd7a-0f2767f7d506",
    "hermana": "7bcf3b89-649e-4eb9-8420-c7c3bc2d8d38",
    "amigo": "c4491183-62c2-4fd6-b8b4-bca2d4e96a78",
    "amiga": "fd94d7fd-a9ad-4d67-8d49-30802215413b",
    "bebe": "54f0f1f6-3eff-4bc5-9ed1-1b43b9804d11",
    "nino": "7653cddb-47d4-4ad0-a247-29960cff7d3e",
    "nina": "f511ad91-68cf-4547-a30e-e9cdd6acebd3",
    "maestro": "7d802559-de13-4dff-bfed-cd8adb2bffb7",
    "medico": "1dd3d584-ffd6-4cbe-9cae-f1e5a476fbcb",
    "familia": "f61c1f1f-5100-4738-9104-1c1b577fc0aa",
    "tia": "ee176523-97a6-4314-863d-952daf76923e",
    "persona": "42e36667-510c-4b0b-907c-2960b5965f7d",
    "hola": "dc163cdf-5060-4c0d-829c-52d5c20dbd57",
    "adios": "9217921b-5623-4666-815e-dee6d2e97175",
    "gracias": "1771fee7-79fd-4b5f-9d98-19eef9eba570",
    "por_favor": "ae2ae99b-43d9-4bc4-9db8-27cc6f8fc0aa",
    "si": "ee103a59-ac65-4d52-a323-4b1e7f80c727",
    "no": "4994a078-bc20-4b24-8923-18808046d06a",
    "lo_siento": "f000bdff-636a-4b43-8884-7c10949938e0",
    "buenos_dias": "18b78501-0f3f-4b5c-8ea9-a888fb4026ca",
    "buenas_tardes": "16e03292-cdfa-4ca4-abfe-8700e3a65288",
    "querer": "b323e5ce-d91c-4476-8390-8924acb8c29b",
    "ir": "9c186374-e6dc-44fe-80df-4e94fdfa774d",
    "comer": "e3171196-1147-48c8-a5d1-27766dadb39b",
    "beber": "42fe2264-649d-40f6-abd4-6c6fdcb3f189",
    "dormir": "471e5a8c-c22b-47f3-8420-de31d22e5fba",
    "jugar": "e9f32c29-1b86-406a-8b25-5635bcf6fa84",
    "hablar": "28700722-d521-42ca-9854-51039b527912",
    "estar": "4f3eb1f3-61e5-4dfe-948a-afe657f88d92",
    "ser": "04157ce9-8655-43a9-af02-ad4d2232de85",
    "gustar": "96a07491-4ee7-46f9-a2a4-98e4111644fb",
    "necesitar": "9a8148ff-c353-4b33-972b-4c0442346ac3",
    "ayudar": "a7280e95-aa23-4e2e-aea9-81d2b7fdd8c3",
    "doler": "63814efb-674a-4850-8c11-e9e831c3a413",
    "dar": "ba90ac32-0542-40a7-925d-f7c03f0d8217",
    "poder": "450dc4cb-d3d5-4d32-9817-7758027fc652",
    "tener": "97af3e21-4c07-452f-951b-7ea4816d93f2",
    "ver": "f8201b59-a880-4f15-bf7e-da9dcbbe020d",
    "leer": "d5de848e-18c2-4f36-a284-0dfb6940134c",
    "escribir": "d0bbb33a-e883-445e-b070-243389d01874",
    "correr": "95563532-3793-49a0-9a19-bf697a1128d4",
    "caminar": "b418b6e6-b461-4c4a-b585-e55a7c1e7833",
    "cantar": "1a18b46a-5420-4ab7-bcab-7add4c00307e",
    "cocinar": "c4e53774-4936-4533-b803-073f30ed2d72",
    "abrir": "f78b200b-751f-4619-abd5-3c4eac6f2531",
    "cerrar": "7f303702-bfdd-4b4e-b866-a73bfd958484",
    "buscar": "2d5e0688-8db6-4125-aef5-20ebc0279b14",
    "esperar": "40bbab1c-1307-4af9-adcd-26f9abbc2024",
    "saltar": "c1dba743-3b02-4fc6-b3c6-9cc0ffe2635f",
    "llorar": "d80a72ef-ae7b-42d9-838d-6741493b71e9",
    "reir": "368d4163-aa43-4356-9c5e-958bc10ee69f",
    "banar": "571e7ada-587c-45a3-b927-370af6f2c0c3",
    "lavar": "baf11fac-24a3-4449-ad8d-9e32a358ed95",
    "contento": "cdd1cf1d-1d27-43b0-95d8-e31f2913b706",
    "triste": "17aea218-6203-4bcb-b2b4-6111a3af87e9",
    "enfadado": "e6a51b8b-da4f-45db-a074-fc0589c6d496",
    "cansado": "9006196c-4737-4a41-8a63-1f32503bd214",
    "asustado": "31cdcc8a-0af0-4b95-9703-659d31729291",
    "nervioso": "4bf64f4e-65e1-49dd-bd2d-5d4984b6fece",
    "preocupado": "2089a31a-af62-4b95-8886-57b6e0f80d21",
    "sorprendido": "84f008af-d760-4124-b4a0-2c47f366654c",
    "tranquilo": "d0cc9e11-ba56-47a6-92c6-4d033951687a",
    "miedo": "bf1c0a6d-6564-427d-83ab-83d5f7aa316b",
    "bien": "ee962cf3-3223-4474-9fea-250d5f485d7c",
    "mal": "4294139f-7a5d-47b5-9cb5-acaa69ea54f7",
    "casa": "39c26164-b0c9-49ed-a43b-860f6a18389b",
    "colegio": "cfd481fa-2a60-4198-991f-1ecae45306fe",
    "parque": "9c458430-76c2-4551-b935-9b2d43300664",
    "tienda": "d0d3a199-b4e1-4f0c-878f-fc4ce4385d8c",
    "playa": "a29f4b41-304c-477b-8138-79aba8dde053",
    "piscina": "a17d83f6-abd9-4c2b-bcac-31cc01ec070e",
    "calle": "ba4b2c61-d7eb-46f2-b681-0179059ff142",
    "restaurante": "b9b4ab47-7325-49eb-ad2c-469ee4b9564e",
    "cocina": "2d04a674-95c9-4368-87b9-d29cf74e9e2e",
    "salon": "4c35391c-cb59-410c-b34b-6c2104241792",
    "dormitorio": "e582a318-3a9f-47b7-8e5a-c09a43495ac5",
    "aula": "4bebe66a-702a-4ad9-8bf5-59ce9a903a5b",
    "clinica": "6fa71cf5-59b4-4cc0-b23b-878f091b8055",
    "supermercado": "cf86b3e5-184b-4270-ad01-fbd57affbbc7",
    "agua": "643ac725-d4d0-4cfd-9cf3-4033190cebea",
    "leche": "31699c3d-4095-41d6-bd41-6bcaad025fca",
    "galleta": "9283ef5d-8939-4757-906b-9b7cc60e867d",
    "pan": "3eb16a9e-5b73-4471-bc8c-70e7d35ef7b0",
    "manzana": "6b77ff0b-fe63-412f-8856-4fae450bbfd1",
    "chocolate": "ff1092d4-22b3-45f8-a336-c23009d5ca2d",
    "arroz": "54f2dde7-69ca-4c9f-8f7c-3bf243411729",
    "huevo": "ba07d664-e0f8-4acc-8a74-cc003a791c34",
    "naranja": "fe2e9a12-d89d-42c1-bd89-6b4b8a1b54ad",
    "platano": "5c2d5eaa-3f9d-4f0b-90d1-afb35b82fcb2",
    "zumo": "e78576fc-4755-4772-a407-29102bc80d69",
    "cafe": "8d7ad184-7ca1-4b41-83d5-01dc41ba8409",
    "yogur": "673ac7b1-3887-4ea3-923d-0ccd94eb8ce4",
    "carne": "ed143119-4ab4-439d-b2c6-47478675bd42",
    "pescado": "90253584-c77a-42e3-b255-0dce0ef1785b",
    "verduras": "5cd87036-1f8f-434f-a9e0-7712fdb167ea",
    "fruta": "f6752d6e-d7d9-4c42-889e-63f5fcad6aa5",
    "comida": "79c314ff-f323-4262-92ba-0519c5ab9729",
    "coche": "e4841955-8f28-4be8-be7e-54918b733f23",
    "autobus": "99d6a295-605f-4acb-bd5f-df97ab2ceae5",
    "bicicleta": "80e904df-2567-4ece-aea7-b1ee0ef9a92a",
    "avion": "6e64518e-5a66-44d1-99b3-c426f0f41ee9",
    "pelota": "fdf24fef-7022-4a9d-b79d-e5c1a990c269",
    "libro": "7915b172-5f48-48cc-bf13-87deb9440249",
    "ordenador": "69888e7b-540b-4f6e-b02a-4e823aefd4b4",
    "telefono": "2c34a71b-284c-46bc-b3a4-da9c1ff52b73",
    "tele": "4b273107-eee4-404e-ac75-ca61da4c7102",
    "cama": "095e3536-1816-4e92-ac8a-c24052c0c8b6",
    "mesa": "0f03876f-9663-4526-820b-499853b30946",
    "silla": "f3a0bd47-0cb0-4dd3-89af-967851b9fbdd",
    "ropa": "aba00bf8-5f4f-45db-b25b-dd5019d9464c",
    "zapatos": "98d41417-4a70-4508-b541-c645ed1f34d5",
    "gafas": "55efefa4-5fbf-4e2a-b883-3fe510c32ffe",
    "lapiz": "fbcf991c-e3c4-4afd-8817-ddf0ba9bc95d",
    "dinero": "baf68230-28da-439d-aba2-d13d937def54",
    "llave": "7db5cae8-04c5-415b-a4be-a445ef09dbdb",
    "puerta": "5919f186-d48a-48d0-af81-c806195e7f78",
    "ventana": "b6e70e2b-5242-49ce-a764-bd26ccc66499",
    "hoy": "a6dba321-440c-4e7b-85e6-ad3640364c33",
    "manana": "adfeaea6-8e56-40a3-a5af-501d4e7aff00",
    "ayer": "2d3f2963-7869-4dd9-b856-d09fb7e737fc",
    "ahora": "b02f13d2-078f-4e1a-9d71-a7c6e52bb42f",
    "despues": "6790ad23-7f92-40b4-b587-a44bc1f691de",
    "antes": "c617423f-edab-46c1-8169-97e0df31919e",
    "noche": "ab584c89-16e4-4555-ac38-205e6ef7a697",
    "dia": "4d647563-5146-49db-b56c-d1b4b34409cf",
    "grande": "d0a64658-5348-4cd1-9972-d15169616d4e",
    "pequeno": "6d0dd884-c209-4994-bd67-b150c2e4aa04",
    "bonito": "b23a9f00-79dc-4da1-8664-fb5da792d902",
    "nuevo": "15ca096c-8c23-4be8-8c56-f030539f0782",
    "caliente": "76ed9310-b41e-4705-8fa9-eb6acaa07205",
    "limpio": "cae0a79d-bad1-474e-bf79-5e8b73df2ed5",
    "sucio": "2c52cf9e-9435-46d6-b77a-66ee7f3f0989",
    "gato": "a05b6aab-4124-4537-b38d-5daa6615e2a7",
    "desayuno": "a0e29714-c423-4f9a-b4eb-a99eca239244",
    "cena": "4a27f27d-b9f4-4ecf-bfd3-9090154524eb",
    "descansar": "f4c08f4c-0012-44ad-b882-7f1de9d3c3fa",
}

# 50 test cases: (keys, human_label)
TESTS = [
    # 1-picto (3)
    (["buenos_dias"], "buenos días"),
    (["por_favor"], "por favor"),
    (["lo_siento"], "lo siento"),
    # 2-picto (5)
    (["querer", "comer"], "querer, comer"),
    (["querer", "jugar"], "querer, jugar"),
    (["estar", "bien"], "estar, bien"),
    (["ir", "casa"], "ir, casa"),
    (["tener", "miedo"], "tener, miedo"),
    # 3-picto (7)
    (["yo", "querer", "galleta"], "yo, querer, galleta"),
    (["yo", "estar", "contento"], "yo, estar, contento"),
    (["yo", "necesitar", "agua"], "yo, necesitar, agua"),
    (["yo", "tener", "miedo"], "yo, tener, miedo"),
    (["mama", "querer", "hablar"], "mamá, querer, hablar"),
    (["yo", "querer", "beber"], "yo, querer, beber"),
    (["ella", "estar", "triste"], "ella, estar, triste"),
    # 4-picto (7)
    (["yo", "querer", "ir", "parque"], "yo, querer, ir, parque"),
    (["yo", "no", "querer", "dormir"], "yo, no, querer, dormir"),
    (["mama", "yo", "tener", "miedo"], "mamá, yo, tener, miedo"),
    (["yo", "no", "gustar", "verduras"], "yo, no, gustar, verduras"),
    (["yo", "querer", "ver", "tele"], "yo, querer, ver, tele"),
    (["yo", "necesitar", "ir", "medico"], "yo, necesitar, ir, médico"),
    (["hermano", "querer", "jugar", "pelota"], "hermano, querer, jugar, pelota"),
    # 5-picto (7)
    (["yo", "querer", "ir", "parque", "manana"], "yo, querer, ir, parque, mañana"),
    (["mama", "yo", "querer", "comer", "pan"], "mamá, yo, querer, comer, pan"),
    (["yo", "estar", "triste", "querer", "casa"], "yo, estar, triste, querer, casa"),
    (["yo", "querer", "jugar", "pelota", "amigo"], "yo, querer, jugar, pelota, amigo"),
    (["mama", "yo", "no", "querer", "colegio"], "mamá, yo, no, querer, colegio"),
    (["yo", "querer", "beber", "zumo", "naranja"], "yo, querer, beber, zumo, naranja"),
    (["yo", "querer", "comer", "arroz", "carne"], "yo, querer, comer, arroz, carne"),
    # 6-picto (6)
    (["mama", "yo", "querer", "ir", "playa", "manana"], "mamá, yo, querer, ir, playa, mañana"),
    (["yo", "estar", "cansado", "querer", "dormir", "casa"], "yo, estar, cansado, querer, dormir, casa"),
    (["papa", "yo", "querer", "jugar", "pelota", "parque"], "papá, yo, querer, jugar, pelota, parque"),
    (["yo", "no", "gustar", "colegio", "querer", "casa"], "yo, no, gustar, colegio, querer, casa"),
    (["mama", "yo", "estar", "enfadado", "no", "hablar"], "mamá, yo, estar, enfadado, no, hablar"),
    (["yo", "estar", "triste", "necesitar", "hablar", "mama"], "yo, estar, triste, necesitar, hablar, mamá"),
    # 7-picto (5)
    (["mama", "yo", "querer", "ir", "casa", "abuela", "manana"], "mamá, yo, querer, ir, casa, abuela, mañana"),
    (["yo", "estar", "cansado", "no", "querer", "ir", "colegio"], "yo, estar, cansado, no, querer, ir, colegio"),
    (["papa", "yo", "querer", "jugar", "pelota", "parque", "amigo"], "papá, yo, querer, jugar, pelota, parque, amigo"),
    (["yo", "querer", "comer", "arroz", "carne", "verduras", "cena"], "yo, querer, comer, arroz, carne, verduras, cena"),
    (["papa", "mama", "yo", "querer", "ir", "playa", "coche"], "papá, mamá, yo, querer, ir, playa, coche"),
    # 8-picto (4)
    (["mama", "yo", "estar", "enfadado", "no", "gustar", "colegio", "hoy"], "mamá, yo, estar, enfadado, no, gustar, colegio, hoy"),
    (["papa", "yo", "querer", "ir", "playa", "coche", "manana", "amigo"], "papá, yo, querer, ir, playa, coche, mañana, amigo"),
    (["yo", "estar", "cansado", "querer", "dormir", "casa", "hoy", "mama"], "yo, estar, cansado, querer, dormir, casa, hoy, mamá"),
    (["yo", "no", "gustar", "verduras", "querer", "comer", "galleta", "chocolate"], "yo, no, gustar, verduras, querer, comer, galleta, chocolate"),
    # 9-picto (3)
    (["mama", "yo", "querer", "ir", "casa", "abuela", "manana", "papa", "coche"], "mamá, yo, querer, ir, casa, abuela, mañana, papá, coche"),
    (["yo", "estar", "cansado", "hoy", "no", "gustar", "colegio", "querer", "casa"], "yo, estar, cansado, hoy, no, gustar, colegio, querer, casa"),
    (["papa", "mama", "yo", "querer", "ir", "playa", "manana", "coche", "amigo"], "papá, mamá, yo, querer, ir, playa, mañana, coche, amigo"),
    # 10-picto (3)
    (["mama", "papa", "yo", "querer", "ir", "playa", "coche", "manana", "amigo", "pelota"], "mamá, papá, yo, querer, ir, playa, coche, mañana, amigo, pelota"),
    (["papa", "yo", "querer", "ir", "restaurante", "comer", "carne", "arroz", "cena", "manana"], "papá, yo, querer, ir, restaurante, comer, carne, arroz, cena, mañana"),
    (["abuela", "abuelo", "yo", "querer", "ir", "casa", "cocinar", "comer", "galleta", "hoy"], "abuela, abuelo, yo, querer, ir, casa, cocinar, comer, galleta, hoy"),
]


def run_test(test_num, keys, label, total):
    """Run a single test and return result dict."""
    # Resolve UUIDs
    uuids = []
    for key in keys:
        uuid = P.get(key)
        if not uuid:
            print(f"[{test_num:2d}/{total}] SKIP     Missing pictogram: '{key}'")
            return {"status": "skip", "error": f"missing: {key}"}
        uuids.append(uuid)

    body = json.dumps({"pictogramIds": uuids}).encode("utf-8")

    req = urllib.request.Request(
        ENDPOINT,
        data=body,
        headers={"Content-Type": "application/json"},
        method="POST",
    )

    start = time.time()
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            data = json.loads(resp.read().decode("utf-8"))
            latency = int((time.time() - start) * 1000)

            variations = data.get("variations", [])
            source = data.get("source", "unknown")
            v1 = variations[0] if len(variations) > 0 else ""
            v2 = variations[1] if len(variations) > 1 else ""
            v3 = variations[2] if len(variations) > 2 else ""

            status = "OK" if source in ("generated", "cached", "cache") else "FALLBACK"

            print(
                f"[{test_num:2d}/{total}] {status:<8s} {latency:5d}ms {source:<9s} | "
                f"{label:<55s} | {v1}"
            )

            return {
                "status": status,
                "source": source,
                "latency_ms": latency,
                "v1": v1,
                "v2": v2,
                "v3": v3,
            }

    except urllib.error.HTTPError as e:
        latency = int((time.time() - start) * 1000)
        error_body = e.read().decode("utf-8", errors="replace")[:200]
        print(
            f"[{test_num:2d}/{total}] ERROR    {latency:5d}ms HTTP_{e.code:<3d} | "
            f"{label:<55s} | {error_body}"
        )
        return {
            "status": f"HTTP_{e.code}",
            "source": "error",
            "latency_ms": latency,
            "error": error_body,
        }
    except Exception as e:
        latency = int((time.time() - start) * 1000)
        print(f"[{test_num:2d}/{total}] ERROR    {latency:5d}ms {str(e)[:50]}")
        return {
            "status": "error",
            "source": "error",
            "latency_ms": latency,
            "error": str(e),
        }


def main():
    total = len(TESTS)
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    print("=" * 75)
    print(f"HablaIA - LLM Test Battery ({MODEL_LABEL})")
    print("=" * 75)
    print(f"Endpoint: {ENDPOINT}")
    print(f"Total tests: {total}")
    print(f"Delay: {DELAY}s between requests")
    print(f"Date: {now}")
    print("=" * 75)
    print()

    results = []
    success = 0
    fallback = 0
    errors = 0
    cache_hits = 0
    generated = 0
    latencies_generated = []
    latencies_cache = []

    for i, (keys, label) in enumerate(TESTS):
        test_num = i + 1
        result = run_test(test_num, keys, label, total)
        result["test"] = test_num
        result["pictograms"] = label
        result["count"] = len(keys)
        results.append(result)

        if result["status"] == "OK":
            success += 1
            if result["source"] in ("cached", "cache"):
                cache_hits += 1
                latencies_cache.append(result["latency_ms"])
            else:
                generated += 1
                latencies_generated.append(result["latency_ms"])
        elif result["status"] == "FALLBACK":
            fallback += 1
        else:
            errors += 1

        # Delay between requests (skip for cache hits and last test)
        if test_num < total:
            time.sleep(DELAY)

    # Summary
    print()
    print("=" * 75)
    print(f"RESULTS SUMMARY - {MODEL_LABEL}")
    print("=" * 75)
    print(f"Total:     {total}")
    print(f"Success:   {success} ({generated} generated + {cache_hits} cache)")
    print(f"Fallback:  {fallback} (concatenation)")
    print(f"Error:     {errors}")
    print(f"Rate:      {(success * 100) // total}%")

    if latencies_generated:
        avg_gen = sum(latencies_generated) // len(latencies_generated)
        min_gen = min(latencies_generated)
        max_gen = max(latencies_generated)
        print(f"\nGenerated latency:")
        print(f"  Min: {min_gen}ms  Avg: {avg_gen}ms  Max: {max_gen}ms")

    if latencies_cache:
        avg_cache = sum(latencies_cache) // len(latencies_cache)
        print(f"Cache latency: avg {avg_cache}ms")

    print("=" * 75)

    # Save results as JSON
    output_file = f"docs/testing/{MODEL_LABEL}-results.json"
    output = {
        "model": MODEL_LABEL,
        "date": now,
        "endpoint": ENDPOINT,
        "total": total,
        "success": success,
        "generated": generated,
        "cache_hits": cache_hits,
        "fallback": fallback,
        "errors": errors,
        "latency_generated_avg_ms": (
            sum(latencies_generated) // len(latencies_generated)
            if latencies_generated
            else 0
        ),
        "latency_generated_min_ms": min(latencies_generated) if latencies_generated else 0,
        "latency_generated_max_ms": max(latencies_generated) if latencies_generated else 0,
        "latency_cache_avg_ms": (
            sum(latencies_cache) // len(latencies_cache) if latencies_cache else 0
        ),
        "results": results,
    }

    with open(output_file, "w", encoding="utf-8") as f:
        json.dump(output, f, indent=2, ensure_ascii=False)

    print(f"Results saved to: {output_file}")


if __name__ == "__main__":
    main()

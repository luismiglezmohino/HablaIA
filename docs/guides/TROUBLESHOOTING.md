# Troubleshooting

Solución de problemas comunes durante la instalación y desarrollo.

---

## Error: "Port 5432 already in use"

PostgreSQL ya está corriendo en tu máquina local. Opciones:
1. Para el PostgreSQL local: `sudo systemctl stop postgresql`
2. Cambia el puerto en `docker-compose.yml`: `"5433:5432"`

## Error: "API key not found" o errores de LLM

**Opción 1 (desarrollo sin API keys):** Usa `PHRASE_PROVIDER=fake` en `backend/.env` - funciona sin claves externas.

**Opción 2 (con LLM real):** Configura las variables según el proveedor:
- Groq (recomendado): `OPENAI_API_KEY` + `OPENAI_BASE_URL` (obtén en https://console.groq.com)
- Gemini: `GEMINI_API_KEY` (obtén en https://aistudio.google.com)
- OpenAI: `OPENAI_API_KEY` (obtén en https://platform.openai.com)

## Error: "Permission denied" al ejecutar scripts

Dale permisos de ejecución:
```bash
chmod +x scripts/*.sh
```

## Frontend no conecta con Backend

En Docker, el proxy de Vite usa `http://backend:8000` (configurado vía `VITE_API_TARGET` en docker-compose). Fuera de Docker, usa `http://localhost:8080` por defecto.

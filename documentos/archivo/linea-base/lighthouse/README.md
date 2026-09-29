# Lighthouse — línea base (tarea 0.3)

Fecha: 25/09/2026. Host local: `https://distribuidoralunic.com.ar.dev/`.

## Archivos

- `*-mobile.json` / `*-desktop.json`: informe Lighthouse por URL.
- `summary.json`: métricas resumidas (Performance, LCP, CLS, transferencia JS/CSS).

## Repetir la medición

Desde `documentos/` (requiere Node y Chrome en `C:\Program Files\Google\Chrome\Application\chrome.exe`):

```bash
npm install lighthouse@12 --no-save
node _lighthouse-run.mjs
```

Si el proceso termina con error `EPERM` al borrar el perfil temporal de Chrome, los JSON suelen haberse generado igual; revisar `summary.json` o volver a extraer con el script en el historial del repo.

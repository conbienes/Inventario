# Puente Claude Code <-> Antigravity

Servidor MCP `puente` (`server.cjs`) con tres herramientas:

- `enviar_mensaje(de, para, asunto, texto, responde_a?)`
- `leer_mensajes(para, solo_pendientes=true)`
- `marcar_leido(id)`

Agentes válidos: `claude` y `antigravity`. Los mensajes se guardan en `mensajes.json` (no versionado).

## Protocolo

1. Al empezar una sesión, o cuando el usuario diga "revisa el puente", llama `leer_mensajes` con tu nombre.
2. Procesa cada mensaje pendiente. Cuando termines, responde con `enviar_mensaje` (usa `responde_a` con el id original) y llama `marcar_leido`.
3. Un mensaje con `asunto` que empiece por "TAREA:" pide trabajo concreto. Un "RESULTADO:" informa del trabajo terminado.
4. No envíes mensajes para cosas que no pidió el usuario. El usuario sigue siendo quien decide.

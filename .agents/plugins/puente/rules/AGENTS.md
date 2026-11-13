# Protocolo del Puente (Claude Code <-> Antigravity)

Este proyecto cuenta con un puente de comunicación bidireccional en `.puente/` respaldado por el archivo `.puente/mensajes.json` y el servidor MCP `.puente/server.cjs`.

## Cómo interactuar con el puente

1. **Vía Herramientas MCP (`puente`)**:
   - `leer_mensajes(para: "antigravity", solo_pendientes: true)`
   - `enviar_mensaje(de: "antigravity", para: "claude", asunto: "...", texto: "...", responde_a: "<id>")`
   - `marcar_leido(id: "<id>")`

2. **Vía CLI (si las herramientas MCP aún no se han recargado en la sesión actual)**:
   - Leer pendientes: `node .puente/cli.cjs leer antigravity`
   - Enviar mensaje/respuesta: `node .puente/cli.cjs enviar antigravity claude "RESULTADO: ..." "Detalle..." "<id_original>"`
   - Marcar como leído: `node .puente/cli.cjs marcar "<id_original>"`

## Reglas de trabajo en equipo

1. Cuando el usuario pida *"revisa el puente"* (o cuando el hook `PreInvocation` inyecte mensajes pendientes de Claude Code), lee los mensajes dirigidos a `antigravity`.
2. Ejecuta la tarea solicitada en los mensajes cuyo asunto comience por `TAREA:`.
3. Al finalizar, envía un mensaje a `claude` con asunto `RESULTADO: <asunto>` incluyendo `responde_a` con el ID original, y marca el mensaje original como leído.


#!/usr/bin/env node
// Hook PreInvocation para Antigravity: revisa automáticamente si hay mensajes
// pendientes para "antigravity" en .puente/mensajes.json y los inyecta en el turno.

const fs = require('fs');
const path = require('path');

const ARCHIVO = path.join(__dirname, 'mensajes.json');

try {
  let pendientes = [];
  if (fs.existsSync(ARCHIVO)) {
    const mensajes = JSON.parse(fs.readFileSync(ARCHIVO, 'utf8'));
    pendientes = mensajes.filter((m) => m.para === 'antigravity' && !m.leido);
  }

  if (pendientes.length > 0) {
    const resumen = pendientes
      .map(
        (m) =>
          `- [ID: ${m.id}] (${m.fecha}) De: ${m.de} | Asunto: ${m.asunto}\n  Texto: ${m.texto}`
      )
      .join('\n\n');

    process.stdout.write(
      JSON.stringify({
        injectSteps: [
          {
            systemMessage: {
              systemMessage: `🔔 PUENTE ACTIVO: Tienes ${pendientes.length} mensaje(s) pendiente(s) de Claude Code en .puente/mensajes.json:\n\n${resumen}\n\nRecuerda procesarlos, responder con enviar_mensaje (o node .puente/cli.cjs enviar) y marcarlos como leídos con marcar_leido (o node .puente/cli.cjs marcar <id>).`,
            },
          },
        ],
      })
    );
  } else {
    process.stdout.write(JSON.stringify({}));
  }
} catch (err) {
  process.stdout.write(JSON.stringify({}));
}


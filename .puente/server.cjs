#!/usr/bin/env node
// Servidor MCP "puente" para que Claude Code y Antigravity se pasen mensajes.
// Sin dependencias: JSON-RPC 2.0 por stdio (un mensaje JSON por línea).
// Almacenamiento: .puente/mensajes.json (no versionado, ver .gitignore).

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const DIR = __dirname;
const ARCHIVO = path.join(DIR, 'mensajes.json');
const AGENTES = ['claude', 'antigravity'];

function leer() {
  try {
    return JSON.parse(fs.readFileSync(ARCHIVO, 'utf8'));
  } catch {
    return [];
  }
}

function escribir(mensajes) {
  const tmp = ARCHIVO + '.tmp';
  fs.writeFileSync(tmp, JSON.stringify(mensajes, null, 2), 'utf8');
  fs.renameSync(tmp, ARCHIVO);
}

const herramientas = [
  {
    name: 'enviar_mensaje',
    description: 'Envía un mensaje al otro agente (claude o antigravity).',
    inputSchema: {
      type: 'object',
      properties: {
        de: { type: 'string', enum: AGENTES, description: 'Quién envía' },
        para: { type: 'string', enum: AGENTES, description: 'Quién recibe' },
        asunto: { type: 'string' },
        texto: { type: 'string', description: 'Contenido completo del mensaje' },
        responde_a: { type: 'string', description: 'ID del mensaje al que responde (opcional)' },
      },
      required: ['de', 'para', 'texto'],
    },
  },
  {
    name: 'leer_mensajes',
    description: 'Lista los mensajes dirigidos a un agente. Por defecto solo los no leídos.',
    inputSchema: {
      type: 'object',
      properties: {
        para: { type: 'string', enum: AGENTES },
        solo_pendientes: { type: 'boolean', default: true },
      },
      required: ['para'],
    },
  },
  {
    name: 'marcar_leido',
    description: 'Marca un mensaje como leído/procesado.',
    inputSchema: {
      type: 'object',
      properties: { id: { type: 'string' } },
      required: ['id'],
    },
  },
];

function llamarHerramienta(nombre, args) {
  if (nombre === 'enviar_mensaje') {
    if (!AGENTES.includes(args.de) || !AGENTES.includes(args.para)) {
      throw new Error('de/para deben ser "claude" o "antigravity"');
    }
    const mensajes = leer();
    const msg = {
      id: crypto.randomUUID(),
      fecha: new Date().toISOString(),
      de: args.de,
      para: args.para,
      asunto: args.asunto || '',
      texto: args.texto,
      responde_a: args.responde_a || null,
      leido: false,
    };
    mensajes.push(msg);
    escribir(mensajes);
    return { ok: true, id: msg.id };
  }

  if (nombre === 'leer_mensajes') {
    const soloPendientes = args.solo_pendientes !== false;
    const lista = leer().filter(
      (m) => m.para === args.para && (!soloPendientes || !m.leido)
    );
    return { mensajes: lista };
  }

  if (nombre === 'marcar_leido') {
    const mensajes = leer();
    const msg = mensajes.find((m) => m.id === args.id);
    if (!msg) throw new Error('Mensaje no encontrado: ' + args.id);
    msg.leido = true;
    escribir(mensajes);
    return { ok: true };
  }

  throw new Error('Herramienta desconocida: ' + nombre);
}

function responder(id, result) {
  process.stdout.write(JSON.stringify({ jsonrpc: '2.0', id, result }) + '\n');
}

function error(id, code, message) {
  process.stdout.write(
    JSON.stringify({ jsonrpc: '2.0', id, error: { code, message } }) + '\n'
  );
}

function manejar(req) {
  const { id, method, params } = req;
  // Notificaciones (sin id) no requieren respuesta.
  if (id === undefined) return;

  switch (method) {
    case 'initialize':
      return responder(id, {
        protocolVersion: (params && params.protocolVersion) || '2024-11-05',
        capabilities: { tools: {} },
        serverInfo: { name: 'puente', version: '1.0.0' },
      });
    case 'ping':
      return responder(id, {});
    case 'tools/list':
      return responder(id, { tools: herramientas });
    case 'tools/call':
      try {
        const out = llamarHerramienta(params.name, params.arguments || {});
        return responder(id, {
          content: [{ type: 'text', text: JSON.stringify(out, null, 2) }],
        });
      } catch (e) {
        return responder(id, {
          content: [{ type: 'text', text: 'Error: ' + e.message }],
          isError: true,
        });
      }
    default:
      return error(id, -32601, 'Método no soportado: ' + method);
  }
}

let buffer = '';
process.stdin.setEncoding('utf8');
process.stdin.on('data', (chunk) => {
  buffer += chunk;
  let i;
  while ((i = buffer.indexOf('\n')) >= 0) {
    const linea = buffer.slice(0, i).trim();
    buffer = buffer.slice(i + 1);
    if (!linea) continue;
    try {
      manejar(JSON.parse(linea));
    } catch (e) {
      error(null, -32700, 'JSON inválido: ' + e.message);
    }
  }
});

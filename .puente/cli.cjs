#!/usr/bin/env node
// CLI auxiliar para interactuar con .puente/mensajes.json desde terminal o scripts.
// Uso:
//   node .puente/cli.cjs leer [antigravity|claude] [--todos]
//   node .puente/cli.cjs enviar <de> <para> <asunto> <texto> [responde_a]
//   node .puente/cli.cjs marcar <id>

const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const ARCHIVO = path.join(__dirname, 'mensajes.json');
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

const [, , accion, ...args] = process.argv;

if (accion === 'leer') {
  const para = args[0] || 'antigravity';
  const todos = args.includes('--todos');
  const lista = leer().filter((m) => m.para === para && (todos || !m.leido));
  console.log(JSON.stringify({ mensajes: lista }, null, 2));
} else if (accion === 'enviar') {
  const [de, para, asunto, texto, responde_a] = args;
  if (!AGENTES.includes(de) || !AGENTES.includes(para) || !texto) {
    console.error('Uso: node .puente/cli.cjs enviar <de> <para> <asunto> <texto> [responde_a]');
    process.exit(1);
  }
  const mensajes = leer();
  const msg = {
    id: crypto.randomUUID(),
    fecha: new Date().toISOString(),
    de,
    para,
    asunto: asunto || '',
    texto,
    responde_a: responde_a || null,
    leido: false,
  };
  mensajes.push(msg);
  escribir(mensajes);
  console.log(JSON.stringify({ ok: true, id: msg.id }, null, 2));
} else if (accion === 'marcar') {
  const [id] = args;
  const mensajes = leer();
  const msg = mensajes.find((m) => m.id === id);
  if (!msg) {
    console.error('Mensaje no encontrado:', id);
    process.exit(1);
  }
  msg.leido = true;
  escribir(mensajes);
  console.log(JSON.stringify({ ok: true, id }, null, 2));
} else {
  console.log('Comandos: leer [agente] [--todos] | enviar <de> <para> <asunto> <texto> [responde_a] | marcar <id>');
}


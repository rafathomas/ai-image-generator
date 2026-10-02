<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const photo = ref(null), referencePhoto = ref(null), prompt = ref(''), edits = ref([]), sending = ref(false), error = ref('');
const examples = ['Melhore a iluminação e deixe a foto com aparência profissional.', 'Troque o fundo por uma praia ao pôr do sol.', 'Coloque a pessoa da foto de referência nesta cena, de forma realista.'];
let timer;
const preview = (file) => file ? URL.createObjectURL(file) : null;
const photoPreview = ref(null), referencePreview = ref(null);
const unavailablePreviews = ref(new Set());

function previewUrl(edit) {
  return edit.result_url || edit.source_url;
}

function isPreviewAvailable(edit) {
  return previewUrl(edit) && !unavailablePreviews.value.has(edit.id);
}

function markPreviewUnavailable(edit) {
  unavailablePreviews.value = new Set([...unavailablePreviews.value, edit.id]);
}

function pick(event, field) {
  const file = event.target.files?.[0]; if (!file) return;
  if (field === 'photo') { photo.value = file; photoPreview.value = preview(file); }
  else { referencePhoto.value = file; referencePreview.value = preview(file); }
}
async function loadEdits() {
  const response = await fetch('/api/photo-edits');
  if (response.ok) edits.value = await response.json();
}
async function submit() {
  error.value = '';
  if (!photo.value || prompt.value.trim().length < 5) { error.value = 'Envie uma foto e descreva a edição com pelo menos 5 caracteres.'; return; }
  sending.value = true;
  try {
    const body = new FormData(); body.append('photo', photo.value); body.append('prompt', prompt.value);
    if (referencePhoto.value) body.append('reference_photo', referencePhoto.value);
    const response = await fetch('/api/photo-edits', { method:'POST', body });
    const data = await response.json();
    if (!response.ok) throw new Error(Object.values(data.errors || {}).flat().join(' ') || 'Não foi possível iniciar a edição.');
    edits.value.unshift(data); prompt.value = '';
  } catch (exception) { error.value = exception.message; } finally { sending.value = false; }
}
onMounted(() => { loadEdits(); timer = window.setInterval(loadEdits, 5000); });
onBeforeUnmount(() => window.clearInterval(timer));
</script>

<template>
  <div class="shell">
    <header class="topbar"><div class="brand"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 2 1.7 6.3L20 10l-6.3 1.7L12 18l-1.7-6.3L4 10l6.3-1.7L12 2Z"/></svg></span> Foto IA</div><span class="tag">Gemini • edição assistida</span></header>
    <section class="hero"><span class="eyebrow">Estúdio de criação</span><h1>Transforme uma foto em algo que você imaginou.</h1><p class="subtitle">Suba a imagem, explique a mudança e, se precisar, use uma segunda foto como referência para criar uma composição.</p></section>
    <section class="workspace">
      <div class="card"><h2>1. Imagem principal</h2><p class="card-intro">JPEG, PNG ou WebP até 10 MB.</p>
        <div class="dropzone"><img v-if="photoPreview" :src="photoPreview" class="preview" alt="Prévia da foto principal" /><label v-else for="photo">Selecionar foto<small>ou arraste o arquivo para cá</small></label><input id="photo" type="file" accept="image/jpeg,image/png,image/webp" @change="pick($event, 'photo')" /></div>
        <div class="two-up"><div class="mini-upload"><label for="reference">+ Foto de referência (opcional)</label><input id="reference" type="file" accept="image/jpeg,image/png,image/webp" @change="pick($event, 'reference')" /><img v-if="referencePreview" :src="referencePreview" class="mini-preview" alt="Prévia da foto de referência" /></div><div class="mini-upload"><strong>Como funciona</strong><p class="card-intro">Use a referência para inserir uma pessoa ou elemento na foto principal.</p></div></div>
      </div>
      <form class="card" @submit.prevent="submit"><h2>2. Direção criativa</h2><p class="card-intro">Seja claro sobre cenário, luz, estilo e posição dos elementos.</p><label class="field-label" for="prompt">O que você quer transformar?</label><textarea id="prompt" v-model="prompt" placeholder="Ex.: Troque o fundo por um escritório moderno e mantenha a pessoa em foco."></textarea><div class="suggestions"><button v-for="example in examples" :key="example" type="button" @click="prompt = example">{{ example }}</button></div><p v-if="error" class="error" role="alert">{{ error }}</p><button class="primary" type="submit" :disabled="sending">{{ sending ? 'Enviando para a IA…' : 'Gerar edição' }}</button></form>
    </section>
    <section class="history"><div class="history-head"><div><h2>Suas edições</h2><p>O resultado aparece aqui assim que a fila terminar.</p></div></div><div class="edits"><p v-if="!edits.length" class="empty">Ainda não há edições. Sua criação vai aparecer aqui.</p><article v-for="edit in edits" :key="edit.id" class="edit card"><img v-if="isPreviewAvailable(edit)" :src="previewUrl(edit)" :alt="edit.result_url ? 'Resultado da edição' : 'Imagem em processamento'" loading="lazy" @error="markPreviewUnavailable(edit)" /><div v-else class="image-unavailable" role="img" aria-label="Prévia indisponível"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 4 16 16M8.5 5.5H5.75A1.75 1.75 0 0 0 4 7.25v11A1.75 1.75 0 0 0 5.75 20h12.5A1.75 1.75 0 0 0 20 18.25v-6.5M14 5.5h4.25A1.75 1.75 0 0 1 20 7.25v3.25M8 16l2.5-2.5 1.7 1.7 1.55-1.55M8.5 10.25h.01" /></svg><span>Prévia indisponível</span></div><div class="edit-info"><p>{{ edit.prompt }}</p><span class="status" :class="edit.status">{{ edit.status === 'completed' ? 'Concluída' : edit.status === 'failed' ? 'Falhou' : edit.status === 'processing' ? 'Processando' : 'Na fila' }}</span></div></article></div></section>
  </div>
</template>

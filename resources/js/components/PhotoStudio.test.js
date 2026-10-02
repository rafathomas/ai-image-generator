import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, it, vi } from 'vitest';
import PhotoStudio from './PhotoStudio.vue';

describe('PhotoStudio', () => {
    afterEach(() => vi.unstubAllGlobals());

    it('shows a useful validation message before submitting without a photo', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue({ ok: true, json: async () => [] }));
        const wrapper = mount(PhotoStudio);
        await wrapper.find('form').trigger('submit.prevent');
        expect(wrapper.text()).toContain('Envie uma foto e descreva a edição');
    });

    it('loads completed edits into the history', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue({ ok: true, json: async () => [{ id: 1, prompt: 'Melhore a luz', status: 'completed', source_url: '/source.jpg', result_url: '/result.jpg' }] }));
        const wrapper = mount(PhotoStudio);
        await vi.waitFor(() => expect(wrapper.text()).toContain('Melhore a luz'));
        expect(wrapper.text()).toContain('Concluída');
    });

    it('replaces a missing history image with a clear fallback', async () => {
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue({ ok: true, json: async () => [{ id: 1, prompt: 'Deixe o carro vermelho', status: 'failed', source_url: '/missing.jpg', result_url: null }] }));
        const wrapper = mount(PhotoStudio);
        await vi.waitFor(() => expect(wrapper.find('img').exists()).toBe(true));
        await wrapper.find('img').trigger('error');
        expect(wrapper.text()).toContain('Prévia indisponível');
        expect(wrapper.find('img').exists()).toBe(false);
    });
});

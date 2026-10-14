/**
 * Shared Alpine components — daftarkan sebelum Alpine.start()
 */
export function registerAlpineComponents(Alpine) {
    Alpine.data('modalCrud', (createOpen = false, editOpen = null) => ({
        createOpen: Boolean(createOpen),
        editOpen: editOpen === null || editOpen === undefined || editOpen === '' ? null : Number(editOpen),

        openCreate() {
            this.createOpen = true;
            this.$nextTick(() => {
                if (typeof window.refreshSelect2 === 'function') {
                    window.refreshSelect2(this.$root);
                }
            });
        },

        openEdit(id) {
            this.editOpen = Number(id);
            this.$nextTick(() => {
                if (typeof window.refreshSelect2 === 'function') {
                    window.refreshSelect2(this.$root);
                }
            });
        },

        closeModals() {
            this.createOpen = false;
            this.editOpen = null;
        },

        init() {
            if (this.createOpen || this.editOpen !== null) {
                this.$nextTick(() => {
                    if (typeof window.refreshSelect2 === 'function') {
                        window.refreshSelect2(this.$root);
                    }
                });
            }
        },
    }));

    Alpine.data('minatWizard', (config = {}) => ({
        step: config.step || 'intro',
        index: 0,
        questions: Array.isArray(config.questions)
            ? config.questions.map((q) => ({
                ...q,
                // Pastikan belum terjawab (jangan coerce null → 0)
                answer: q.answer === null || q.answer === undefined || q.answer === ''
                    ? null
                    : String(q.answer),
            }))
            : [],
        jenjang: config.jenjang || '',
        requireJenjang: Boolean(config.requireJenjang),

        get total() {
            return this.questions.length;
        },

        get current() {
            return this.questions[this.index] || null;
        },

        get currentText() {
            return this.current ? this.current.text : '';
        },

        get currentOptions() {
            return this.current && Array.isArray(this.current.options) ? this.current.options : [];
        },

        get currentAnswered() {
            return this.hasAnswer(this.current);
        },

        get answeredCount() {
            return this.questions.filter((q) => this.hasAnswer(q)).length;
        },

        get progress() {
            return this.total ? Math.round((this.answeredCount / this.total) * 100) : 0;
        },

        get allAnswered() {
            return this.total > 0 && this.answeredCount === this.total;
        },

        hasAnswer(question) {
            return question !== null
                && question !== undefined
                && question.answer !== null
                && question.answer !== undefined
                && question.answer !== '';
        },

        isSelected(optionIndex) {
            return this.current && String(this.current.answer) === String(optionIndex);
        },

        select(optionIndex) {
            if (! this.current) {
                return;
            }
            // Simpan pilihan saja — jangan auto-advance (klik bisa "nyangkut" ke soal berikutnya).
            this.current.answer = String(optionIndex);
        },

        prev() {
            if (this.index > 0) {
                this.index--;
            }
        },

        next() {
            if (! this.currentAnswered) {
                return;
            }
            if (this.index < this.total - 1) {
                this.index++;
            }
        },

        start() {
            this.step = 'quiz';
            this.index = 0;
        },

        init() {
            const refresh = () => {
                this.$nextTick(() => {
                    if (typeof window.refreshSelect2 === 'function') {
                        window.refreshSelect2(this.$el);
                    }
                });
            };
            refresh();
            this.$watch('step', refresh);
        },
    }));

    /**
     * Chatbot siswa — typing indicator + typewriter agar tidak kaku.
     * options: { messages, quickMessages, endpoint, csrf, floating, open }
     */
    Alpine.data('siswaChatbot', (options = {}) => ({
        open: Boolean(options.open ?? false),
        floating: Boolean(options.floating ?? false),
        message: '',
        sending: false,
        revealing: false,
        messages: Array.isArray(options.messages)
            ? options.messages.map((item, index) => ({
                id: item.id || `seed-${index}`,
                role: item.role || 'assistant',
                text: item.text || '',
                streaming: false,
            }))
            : [],
        quickMessages: Array.isArray(options.quickMessages) ? options.quickMessages : [],
        endpoint: options.endpoint || '',
        csrf: options.csrf || '',

        get busy() {
            return this.sending || this.revealing;
        },

        /** Escape + format ringan (bold, link, baris baru) agar jawaban PCR/BK mudah dibaca. */
        formatChatHtml(text) {
            const raw = String(text ?? '');
            const escaped = raw
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');

            return escaped
                .replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold text-inherit">$1</strong>')
                .replace(
                    /(https?:\/\/[^\s<]+)/g,
                    '<a href="$1" target="_blank" rel="noopener noreferrer" class="chat-md-link">$1</a>',
                )
                .replace(/\n/g, '<br>');
        },

        sleep(ms) {
            return new Promise((resolve) => setTimeout(resolve, ms));
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const body = this.$refs.chatBody;
                if (body) {
                    body.scrollTop = body.scrollHeight;
                }
            });
        },

        toggle() {
            this.open = ! this.open;
            if (this.open) {
                this.scrollToBottom();
                this.$nextTick(() => this.$refs.chatInput?.focus());
            }
        },

        useQuickMessage(text) {
            if (this.busy) {
                return;
            }
            this.send(text);
        },

        async revealText(fullText) {
            this.revealing = true;
            const id = `a-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
            this.messages.push({
                id,
                role: 'assistant',
                text: '',
                streaming: true,
            });
            this.scrollToBottom();

            const chars = Array.from(fullText);
            const len = chars.length;
            const chunk = len > 500 ? 5 : len > 220 ? 3 : len > 80 ? 2 : 1;
            const delay = len > 500 ? 6 : len > 220 ? 10 : 16;

            let i = 0;
            while (i < len) {
                i = Math.min(i + chunk, len);
                const idx = this.messages.findIndex((m) => m.id === id);
                if (idx === -1) {
                    break;
                }
                this.messages[idx] = {
                    ...this.messages[idx],
                    text: chars.slice(0, i).join(''),
                };
                if (i === len || i % (chunk * 6) === 0) {
                    this.scrollToBottom();
                }
                await this.sleep(delay);
            }

            const idx = this.messages.findIndex((m) => m.id === id);
            if (idx !== -1) {
                this.messages[idx] = {
                    ...this.messages[idx],
                    text: fullText,
                    streaming: false,
                };
            }
            this.revealing = false;
            this.scrollToBottom();
        },

        async send(preset = null) {
            const text = String(preset ?? this.message).trim();
            if (! text || this.busy || ! this.endpoint) {
                return;
            }

            this.messages.push({
                id: `u-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
                role: 'user',
                text,
                streaming: false,
            });
            this.message = '';
            this.sending = true;
            this.scrollToBottom();

            let reply = '';
            try {
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ message: text }),
                });

                let data = {};
                try {
                    data = await response.json();
                } catch (_) {
                    data = {};
                }

                if (! response.ok) {
                    reply = data.reply
                        || 'Maaf, chatbot sedang sibuk. Coba kirim ulang sebentar lagi ya.';
                } else {
                    reply = (typeof data.reply === 'string' && data.reply.trim() !== '')
                        ? data.reply.trim()
                        : 'Aku belum bisa menangkap maksudnya. Coba tulis dengan kalimat yang lebih jelas ya.';
                }
            } catch (_) {
                reply = 'Maaf, koneksi chatbot sedang bermasalah. Pastikan layanan chatbot berjalan, lalu coba lagi.';
            } finally {
                this.sending = false;
            }

            await this.revealText(reply);
            this.$nextTick(() => this.$refs.chatInput?.focus());
        },
    }));
}

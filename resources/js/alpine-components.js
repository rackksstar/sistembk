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
}

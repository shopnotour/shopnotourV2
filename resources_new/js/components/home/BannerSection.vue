<template>
    <section class="py-10 overflow-hidden">
        <div class="container mx-auto px-4">

            <div class="truth-track">
                <div
                    v-for="(item, index) in sentences"
                    :key="index"
                    class="truth-slide"
                    :class="{ active: current === index, leaving: leaving === index }"
                    :style="{ background: item.bg }">

                    <div class="truth-inner">
                        <span class="truth-icon">{{ item.icon }}</span>
                        <div>
                            <p class="truth-label">{{ item.label }}</p>
                            <p class="truth-text">{{ item.text }}</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>
</template>

<script>
export default {
    name: 'TruthBanner',

    props: {
        sentences: {
            type: Array,
            default: () => [
                {
                    icon: '✈️',
                    label: 'সর্বোত্তম মূল্য গ্যারান্টি',
                    text: 'যেকোনো কম দামে আমরা মিলিয়ে দেব — কোনো প্রশ্ন ছাড়াই।',
                    bg: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                },
                {
                    icon: '🔒',
                    label: '১০০% নিরাপদ বুকিং',
                    text: 'আপনার পেমেন্ট ও ব্যক্তিগত তথ্য সম্পূর্ণ সুরক্ষিত।',
                    bg: 'linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%)'
                },
                {
                    icon: '🌍',
                    label: 'বিশ্বব্যাপী কভারেজ',
                    text: '১২০টি দেশের ৫০০+ গন্তব্যে ফ্লাইট বুক করুন সহজেই।',
                    bg: 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)'
                },
                {
                    icon: '⚡',
                    label: 'তাৎক্ষণিক নিশ্চিতকরণ',
                    text: 'বুকিংয়ের কয়েক সেকেন্ডের মধ্যেই আপনার ই-টিকেট পান।',
                    bg: 'linear-gradient(135deg, #f7971e 0%, #ffd200 100%)'
                },
                {
                    icon: '🎧',
                    label: '২৪/৭ সাপোর্ট',
                    text: 'দিনে বা রাতে — আমাদের টিম সবসময় আপনার পাশে আছে।',
                    bg: 'linear-gradient(135deg, #cb2d3e 0%, #ef473a 100%)'
                },
                {
                    icon: '🏆',
                    label: 'পুরস্কারজয়ী সেবা',
                    text: 'বাংলাদেশের সেরা ফ্লাইট বুকিং প্ল্যাটফর্ম হিসেবে স্বীকৃত।',
                    bg: 'linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%)'
                },
            ]
        },
        interval: {
            type: Number,
            default: 10000
        }
    },

    data() {
        return {
            current: 0,
            leaving: -1,
            timer: null
        };
    },

    mounted() {
        this.startTimer();
    },

    beforeDestroy() {
        clearInterval(this.timer);
    },

    methods: {
        startTimer() {
            clearInterval(this.timer);
            this.timer = setInterval(() => {
                this.leaving = this.current;
                this.current = (this.current + 1) % this.sentences.length;

                // leaving class 700ms পর সরাও
                setTimeout(() => {
                    this.leaving = -1;
                }, 700);
            }, this.interval);
        }
    }
}
</script>

<style scoped>
/* ── wrapper: fixed height so cards stack ── */
.truth-track {
    position: relative;
    height: 130px;
    width: 83.333%; /* col-10 */
    margin: 0 auto;
}

/* ── all cards: absolute, stacked ── */
.truth-slide {
    position: absolute;
    inset: 0;
    border-radius: 20px;
    padding: 28px 40px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    opacity: 0;
    transform: translateX(100%);
    transition: opacity 0.7s ease, transform 0.7s ease;
    pointer-events: none;
}

.truth-slide.active {
    opacity: 1;
    transform: translateX(0);
    pointer-events: auto;
}

.truth-slide.leaving {
    opacity: 0;
    transform: translateX(-100%);
}

/* ── inner layout ── */
.truth-inner {
    display: flex;
    align-items: center;
    gap: 24px;
    height: 100%;
}

.truth-icon {
    font-size: 44px;
    flex-shrink: 0;
    filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.2));
}

.truth-label {
    font-size: 11px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.65);
    text-transform: uppercase;
    letter-spacing: 0.12em;
    margin: 0 0 6px 0;
}

.truth-text {
    font-size: 20px;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.45;
    margin: 0;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
}

/* ── mobile ── */
@media (max-width: 768px) {
    .truth-track {
        width: 100%;
        height: 150px;
    }
    .truth-slide { padding: 22px 20px; }
    .truth-text  { font-size: 16px; }
    .truth-icon  { font-size: 32px; }
}
</style>

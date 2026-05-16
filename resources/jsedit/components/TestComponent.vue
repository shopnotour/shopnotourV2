<template>
    <div style="padding: 20px; background: #f0f0f0; border: 2px solid #4CAF50; border-radius: 8px;">
        <h2>✅ Vue 2 Working!</h2>
        <h1>sarowar</h1>
        <p style="font-size: 20px; font-weight: bold;">Count: {{ count }}</p>
        <button
            @click="increment"
            style="padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer; border-radius: 4px; font-size: 16px;">
            Click Me
        </button>
    </div>
</template>

<script>
export default {
    name: 'TestComponent',
    data() {
        return {
            count: 0
        }
    },
    methods: {
        increment() {
            this.count++;
        }
    }
}
</script>

<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <meta name="robots" content="noindex, nofollow">
        <meta name="googlebot" content="noindex, nofollow">
    </Head>
    
    <div class="min-h-screen bg-slate-900 px-4 font-sans text-gray-900 flex items-center justify-center">
        <slot />
    </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'

const form = useForm({ code: '' })
const resendForm = useForm({})

const submit = () => form.post('/2fa/verify')
const resend = () => resendForm.post('/2fa/resend')
</script>

<template>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f8fafc;font-family:'Segoe UI',sans-serif">
    <div style="width:100%;max-width:420px;background:white;border-radius:16px;border:1px solid #e2e8f0;padding:2.5rem">

        <div style="text-align:center;margin-bottom:2rem">
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                <svg width="26" height="26" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin-bottom:6px">Verificacion en dos pasos</h1>
            <p style="font-size:13px;color:#64748b;line-height:1.5">Hemos enviado un codigo de 6 digitos a tu correo electronico. Ingresalo a continuacion.</p>
        </div>

        <div v-if="form.errors.code" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
            {{ form.errors.code }}
        </div>

        <div v-if="resendForm.recentlySuccessful" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
            Codigo reenviado correctamente.
        </div>

        <div style="margin-bottom:1.5rem">
            <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Codigo de verificacion</label>
            <input v-model="form.code" type="text" maxlength="6" placeholder="000000"
                style="width:100%;padding:14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:24px;outline:none;box-sizing:border-box;margin-top:6px;text-align:center;letter-spacing:10px;font-weight:600"
                @focus="$event.target.style.borderColor='#f97316'"
                @blur="$event.target.style.borderColor='#e2e8f0'"/>
        </div>

        <button @click="submit" :disabled="form.processing"
            style="width:100%;padding:13px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;margin-bottom:1rem">
            {{ form.processing ? 'Verificando...' : 'Verificar codigo' }}
        </button>

        <div style="text-align:center">
            <span style="font-size:13px;color:#64748b">No recibiste el codigo? </span>
            <button @click="resend" :disabled="resendForm.processing"
                style="background:none;border:none;color:#f97316;font-size:13px;font-weight:600;cursor:pointer;padding:0">
                {{ resendForm.processing ? 'Enviando...' : 'Reenviar' }}
            </button>
        </div>

    </div>
</div>
</template>
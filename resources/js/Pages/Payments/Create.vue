<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { user } = defineProps(['user'])
const logout = () => router.post('/logout')
const role = usePage().props.auth.user.roles?.[0] ?? 'sin rol'

const form = useForm({
    method:    'efectivo',
    amount:    '',
    reference: '',
    notes:     '',
})

const submit = () => form.post('/pagos')
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <!-- Navbar -->
    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="/dashboard" style="font-size:13px;color:#64748b;text-decoration:none">Dashboard</a>
            <a href="/perfil" style="font-size:13px;color:#64748b;text-decoration:none">Mi perfil</a>
            <button @click="logout" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:600px;margin:2rem auto;padding:0 1rem">

        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Realizar pago</h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0">Selecciona tu metodo de pago preferido</p>
        </div>

        <!-- Seleccion de metodo -->
        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:2rem;margin-bottom:1.5rem">
            <h2 style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:1rem">Metodo de pago</h2>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:1.5rem">
                <div @click="form.method='efectivo'"
                    :style="`padding:1.5rem;border-radius:10px;border:2px solid;text-align:center;cursor:pointer;${form.method==='efectivo' ? 'border-color:#f97316;background:#fff7ed' : 'border-color:#e2e8f0;background:white'}`">
                    <svg style="margin:0 auto 8px;display:block" width="28" height="28" fill="none" :stroke="form.method==='efectivo' ? '#f97316' : '#94a3b8'" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                    <div :style="`font-size:14px;font-weight:600;${form.method==='efectivo' ? 'color:#f97316' : 'color:#64748b'}`">Efectivo</div>
                    <div style="font-size:12px;color:#94a3b8;margin-top:2px">Pago en caja</div>
                </div>
                <div @click="form.method='transferencia'"
                    :style="`padding:1.5rem;border-radius:10px;border:2px solid;text-align:center;cursor:pointer;${form.method==='transferencia' ? 'border-color:#f97316;background:#fff7ed' : 'border-color:#e2e8f0;background:white'}`">
                    <svg style="margin:0 auto 8px;display:block" width="28" height="28" fill="none" :stroke="form.method==='transferencia' ? '#f97316' : '#94a3b8'" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <div :style="`font-size:14px;font-weight:600;${form.method==='transferencia' ? 'color:#f97316' : 'color:#64748b'}`">Transferencia</div>
                    <div style="font-size:12px;color:#94a3b8;margin-top:2px">Pago bancario</div>
                </div>
            </div>

            <!-- Info transferencia -->
            <div v-if="form.method==='transferencia'" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:1rem;margin-bottom:1rem">
                <div style="font-size:13px;font-weight:600;color:#15803d;margin-bottom:6px">Datos bancarios</div>
                <div style="font-size:13px;color:#166534">Banco: Bancolombia</div>
                <div style="font-size:13px;color:#166534">Cuenta: 123-456789-00</div>
                <div style="font-size:13px;color:#166534">Titular: Mi Chuzito SAS</div>
            </div>

            <div style="margin-bottom:1rem">
                <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Monto a pagar</label>
                <div style="position:relative;margin-top:6px">
                    <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#64748b;font-weight:500">$</span>
                    <input v-model="form.amount" type="number" step="0.01" placeholder="0.00"
                        style="width:100%;padding:11px 12px 11px 28px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                </div>
                <span v-if="form.errors.amount" style="color:#dc2626;font-size:11px">{{ form.errors.amount }}</span>
            </div>

            <div v-if="form.method==='transferencia'" style="margin-bottom:1rem">
                <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Numero de referencia</label>
                <input v-model="form.reference" placeholder="Ej: 123456789"
                    style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                    @focus="$event.target.style.borderColor='#f97316'"
                    @blur="$event.target.style.borderColor='#e2e8f0'"/>
                <span v-if="form.errors.reference" style="color:#dc2626;font-size:11px">{{ form.errors.reference }}</span>
            </div>

            <div style="margin-bottom:1.5rem">
                <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Notas adicionales (opcional)</label>
                <textarea v-model="form.notes" placeholder="Informacion adicional..."
                    style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px;resize:vertical;min-height:80px"
                    @focus="$event.target.style.borderColor='#f97316'"
                    @blur="$event.target.style.borderColor='#e2e8f0'"></textarea>
            </div>

            <!-- Seguridad PCI-DSS -->
            <div style="background:#f8fafc;border-radius:8px;padding:12px;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px">
                <svg width="20" height="20" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#1e293b">Pago seguro PCI-DSS</div>
                    <div style="font-size:11px;color:#64748b">Tus datos estan protegidos con encriptacion</div>
                </div>
            </div>

            <button @click="submit" :disabled="form.processing"
                style="width:100%;padding:13px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer">
                {{ form.processing ? 'Procesando...' : 'Confirmar pago' }}
            </button>
        </div>

    </div>
</div>
</template>
<script setup>
import { router } from '@inertiajs/vue3'

const { orders } = defineProps(['orders'])

const statusColors = {
    en_proceso: { bg: '#fff7ed', color: '#c2410c', label: 'En proceso' },
    en_camino:  { bg: '#eff6ff', color: '#1d4ed8', label: 'En camino' },
    entregado:  { bg: '#f0fdf4', color: '#15803d', label: 'Entregado' },
    cancelado:  { bg: '#fef2f2', color: '#dc2626', label: 'Cancelado' },
}

const formatPrice = (p) => '$' + Number(p).toLocaleString('es-CO')
const formatDate  = (d) => d ? new Date(d).toLocaleString('es-CO', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }) : '-'

// Usamos router.patch directo (no useForm) y pedimos a Inertia que refresque solo "orders"
const updateStatus = (orderId, newStatus) => {
    if (confirm(newStatus === 'entregado' ? 'Confirmar entrega del pedido?' : 'Marcar pedido en camino?')) {
        router.patch(`/orders/${orderId}/status`, { status: newStatus }, {
            preserveScroll: true,
            preserveState: true,
            only: ['orders'], // refresca solo los datos de pedidos, sin recargar toda la pagina
        })
    }
}

const pendientes  = orders.filter(o => o.status !== 'entregado' && o.status !== 'cancelado')
const completados = orders.filter(o => o.status === 'entregado' || o.status === 'cancelado')
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px;position:sticky;top:0;z-index:100">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="/perfil" style="font-size:13px;color:#64748b;text-decoration:none;display:flex;align-items:center;gap:4px">
                <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Mi perfil
            </a>
            <span style="font-size:12px;padding:3px 10px;border-radius:20px;background:#f0fdf4;color:#059669;font-weight:500">Repartidor</span>
            <button @click="router.post('/logout')" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:900px;margin:2rem auto;padding:0 1rem">

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:2rem">
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.25rem;text-align:center">
                <div style="font-size:28px;font-weight:800;color:#f97316">{{ pendientes.length }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px">Entregas pendientes</div>
            </div>
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.25rem;text-align:center">
                <div style="font-size:28px;font-weight:800;color:#15803d">{{ completados.length }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px">Completadas hoy</div>
            </div>
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.25rem;text-align:center">
                <div style="font-size:28px;font-weight:800;color:#1d4ed8">{{ orders.length }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px">Total asignadas</div>
            </div>
        </div>

        <div style="margin-bottom:2rem">
            <h2 style="font-size:17px;font-weight:700;color:#1e293b;margin-bottom:1rem">Entregas pendientes</h2>

            <div v-if="pendientes.length === 0" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:3rem;text-align:center">
                <svg width="48" height="48" fill="none" stroke="#e2e8f0" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <div style="font-size:15px;font-weight:600;color:#64748b">No tienes entregas pendientes</div>
                <div style="font-size:13px;color:#94a3b8;margin-top:4px">Las nuevas entregas apareceran aqui automaticamente</div>
            </div>

            <div v-for="order in pendientes" :key="order.id"
                style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1rem;border-left:4px solid #f97316">

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
                    <div>
                        <div style="font-size:16px;font-weight:700;color:#1e293b">Pedido #{{ order.id }}</div>
                        <div style="font-size:12px;color:#94a3b8;margin-top:2px">{{ formatDate(order.created_at) }}</div>
                    </div>
                    <span :style="`font-size:12px;padding:4px 12px;border-radius:20px;font-weight:500;background:${statusColors[order.status]?.bg};color:${statusColors[order.status]?.color}`">
                        {{ statusColors[order.status]?.label }}
                    </span>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:1rem">
                    <div style="background:#f8fafc;border-radius:8px;padding:12px">
                        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:6px">Cliente</div>
                        <div style="font-size:14px;font-weight:500;color:#1e293b">{{ order.cliente?.name }}</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px">{{ order.cliente?.phone ?? 'Sin telefono' }}</div>
                    </div>
                    <div style="background:#fff7ed;border-radius:8px;padding:12px">
                        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:6px">Valor entrega</div>
                        <div style="font-size:18px;font-weight:800;color:#f97316">{{ formatPrice(order.total) }}</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px">
                            {{ order.payment_method }}
                            <span v-if="order.payment_method === 'efectivo'" style="color:#15803d;font-weight:500">— Pagado</span>
                            <span v-else style="color:#c2410c;font-weight:500">— Pendiente confirmacion</span>
                        </div>
                    </div>
                </div>

                <div style="background:#eff6ff;border-radius:8px;padding:12px;margin-bottom:1rem;display:flex;align-items:flex-start;gap:8px">
                    <svg width="16" height="16" fill="none" stroke="#1d4ed8" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#1d4ed8;text-transform:uppercase;margin-bottom:2px">Direccion de entrega</div>
                        <div style="font-size:14px;font-weight:500;color:#1e293b">{{ order.address }}</div>
                    </div>
                </div>

                <div style="margin-bottom:1rem">
                    <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:6px">Productos</div>
                    <div v-for="item in order.items" :key="item.id" style="font-size:13px;color:#64748b;padding:3px 0">
                        <div style="display:flex;justify-content:space-between">
                            <span>{{ item.name }} x{{ item.qty }}</span>
                            <span style="font-weight:500;color:#1e293b">{{ formatPrice(item.subtotal) }}</span>
                        </div>
                        <div v-if="item.note" style="font-size:12px;color:#94a3b8;margin-top:2px">Nota: {{ item.note }}</div>
                    </div>
                </div>

                <div v-if="order.notes" style="background:#fef9c3;border-radius:8px;padding:10px;margin-bottom:1rem;font-size:13px;color:#854d0e">
                    <strong>Nota general:</strong> {{ order.notes }}
                </div>

                <div style="display:flex;gap:8px">
                    <button v-if="order.status === 'en_proceso'" @click="updateStatus(order.id, 'en_camino')"
                        style="flex:1;padding:11px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                        <svg width="16" height="16" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        Marcar en camino
                    </button>
                    <button v-if="order.status === 'en_camino'" @click="updateStatus(order.id, 'entregado')"
                        style="flex:1;padding:11px;background:linear-gradient(135deg,#22c55e,#15803d);color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                        <svg width="16" height="16" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Confirmar entrega
                    </button>
                </div>
            </div>
        </div>

        <div v-if="completados.length > 0">
            <h2 style="font-size:17px;font-weight:700;color:#1e293b;margin-bottom:1rem">Historial de entregas</h2>
            <div v-for="order in completados" :key="order.id"
                style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.25rem;margin-bottom:0.75rem;opacity:0.8">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#1e293b">Pedido #{{ order.id }}</div>
                        <div style="font-size:12px;color:#94a3b8">{{ order.address }}</div>
                        <div style="font-size:12px;color:#94a3b8">{{ formatDate(order.delivered_at ?? order.cancelled_at) }}</div>
                    </div>
                    <div style="text-align:right">
                        <span :style="`font-size:12px;padding:4px 12px;border-radius:20px;font-weight:500;background:${statusColors[order.status]?.bg};color:${statusColors[order.status]?.color}`">
                            {{ statusColors[order.status]?.label }}
                        </span>
                        <div style="font-size:15px;font-weight:700;color:#f97316;margin-top:4px">{{ formatPrice(order.total) }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</template>
<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { products, agotados, bajos } = defineProps(['products', 'agotados', 'bajos'])

const editForm   = useForm({ stock: 0, is_available: true, price: 0, image: null })
const createForm = useForm({ name: '', description: '', price: '', category: '', stock: '', image: null })
const editingId   = ref(null)
const showCreate  = ref(false)
const createPreview = ref(null)
const editPreview   = ref(null)

const startEdit = (product) => {
    editingId.value = product.id
    editForm.stock        = product.stock
    editForm.is_available = product.is_available
    editForm.price        = product.price
    editForm.image        = null
    editPreview.value     = null
}

const onCreateImageChange = (e) => {
    const file = e.target.files[0]
    createForm.image = file
    if (file) createPreview.value = URL.createObjectURL(file)
}

const onEditImageChange = (e) => {
    const file = e.target.files[0]
    editForm.image = file
    if (file) editPreview.value = URL.createObjectURL(file)
}

const saveEdit = (productId) => {
    editForm.post(`/admin/inventory/${productId}`, {
        forceFormData: true,
        onSuccess: () => { editingId.value = null; editPreview.value = null }
    })
}

const deleteProduct = (productId) => {
    if (confirm('Eliminar este producto?')) {
        router.delete(`/admin/inventory/${productId}`)
    }
}

const submitCreate = () => {
    createForm.post('/admin/inventory', {
        forceFormData: true,
        onSuccess: () => { showCreate.value = false; createForm.reset(); createPreview.value = null }
    })
}

const formatPrice = (p) => '$' + Number(p).toLocaleString('es-CO')
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px;position:sticky;top:0;z-index:100">
        <div style="display:flex;align-items:center;gap:10px;cursor:pointer" @click="router.visit('/dashboard')">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="/admin/users" style="font-size:13px;color:#64748b;text-decoration:none">Usuarios</a>
            <a href="/admin/orders" style="font-size:13px;color:#64748b;text-decoration:none">Pedidos</a>
            <a href="/admin/payments" style="font-size:13px;color:#64748b;text-decoration:none">Pagos</a>
            <span style="font-size:12px;padding:3px 10px;border-radius:20px;background:#f3e8ff;color:#7c3aed;font-weight:500">Administrador</span>
            <button @click="router.post('/logout')" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:1100px;margin:2rem auto;padding:0 1rem">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
            <div>
                <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Gestion de inventario</h1>
                <p style="font-size:13px;color:#64748b;margin:4px 0 0">Administra productos, precios, stock e imagenes</p>
            </div>
            <button @click="showCreate=!showCreate"
                style="padding:10px 20px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                + Nuevo producto
            </button>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">
            <div style="background:white;border-radius:10px;border:1px solid #e2e8f0;padding:1rem;text-align:center">
                <div style="font-size:28px;font-weight:800;color:#f97316">{{ products.length }}</div>
                <div style="font-size:12px;color:#64748b">Total productos</div>
            </div>
            <div style="background:white;border-radius:10px;border:1px solid #fef2f2;padding:1rem;text-align:center">
                <div style="font-size:28px;font-weight:800;color:#dc2626">{{ agotados }}</div>
                <div style="font-size:12px;color:#64748b">Agotados</div>
            </div>
            <div style="background:white;border-radius:10px;border:1px solid #fff7ed;padding:1rem;text-align:center">
                <div style="font-size:28px;font-weight:800;color:#c2410c">{{ bajos }}</div>
                <div style="font-size:12px;color:#64748b">Stock bajo (5 o menos)</div>
            </div>
        </div>

        <!-- Formulario crear producto -->
        <div v-if="showCreate" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1.5rem">
            <h3 style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:1rem">Nuevo producto</h3>

            <div style="display:flex;gap:1.5rem;margin-bottom:1rem">
                <!-- Vista previa imagen -->
                <div style="flex-shrink:0">
                    <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Imagen</label>
                    <div style="margin-top:4px;width:120px;height:120px;border-radius:10px;border:2px dashed #e2e8f0;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f8fafc;position:relative">
                        <img v-if="createPreview" :src="createPreview" style="width:100%;height:100%;object-fit:cover"/>
                        <svg v-else width="32" height="32" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <input type="file" accept="image/jpeg,image/jpg,image/png,image/webp" @change="onCreateImageChange"
                            style="position:absolute;inset:0;opacity:0;cursor:pointer"/>
                    </div>
                    <span v-if="createForm.errors.image" style="color:#dc2626;font-size:11px;display:block;margin-top:4px;max-width:120px">{{ createForm.errors.image }}</span>
                </div>

                <div style="flex:1;display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Nombre</label>
                        <input v-model="createForm.name" placeholder="Nombre del producto"
                            style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px"
                            @focus="$event.target.style.borderColor='#f97316'" @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Categoria</label>
                        <input v-model="createForm.category" placeholder="Ej: Chuzo de Res"
                            style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px"
                            @focus="$event.target.style.borderColor='#f97316'" @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Precio</label>
                        <input v-model="createForm.price" type="number" placeholder="0"
                            style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px"
                            @focus="$event.target.style.borderColor='#f97316'" @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Stock inicial</label>
                        <input v-model="createForm.stock" type="number" placeholder="0"
                            style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px"
                            @focus="$event.target.style.borderColor='#f97316'" @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                    <div style="grid-column:span 2">
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Descripcion</label>
                        <input v-model="createForm.description" placeholder="Descripcion del producto"
                            style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px"
                            @focus="$event.target.style.borderColor='#f97316'" @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:8px">
                <button @click="submitCreate" :disabled="createForm.processing"
                    style="padding:9px 20px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                    {{ createForm.processing ? 'Subiendo...' : 'Crear producto' }}
                </button>
                <button @click="showCreate=false;createForm.reset();createPreview=null"
                    style="padding:9px 20px;background:#f1f5f9;color:#64748b;border:none;border-radius:8px;font-size:13px;cursor:pointer">
                    Cancelar
                </button>
            </div>
        </div>

        <!-- Tabla inventario -->
        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Producto</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Categoria</th>
                        <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Precio</th>
                        <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Stock</th>
                        <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Estado</th>
                        <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products" :key="product.id" style="border-top:1px solid #f1f5f9">
                        <td style="padding:12px 16px">
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="position:relative;width:44px;height:44px;border-radius:8px;overflow:hidden;flex-shrink:0;background:#f1f5f9">
                                    <img :src="editingId === product.id && editPreview ? editPreview : product.image_url_full" :alt="product.name" style="width:100%;height:100%;object-fit:cover"/>
                                    <input v-if="editingId === product.id" type="file" accept="image/jpeg,image/jpg,image/png,image/webp" @change="onEditImageChange"
                                        style="position:absolute;inset:0;opacity:0;cursor:pointer"/>
                                </div>
                                <div>
                                    <div style="font-size:14px;font-weight:500;color:#1e293b">{{ product.name }}</div>
                                    <div style="font-size:11px;color:#94a3b8">{{ product.description?.slice(0,40) }}...</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px 16px;font-size:13px;color:#64748b">{{ product.category }}</td>
                        <td style="padding:12px 16px;text-align:center">
                            <template v-if="editingId === product.id">
                                <input v-model="editForm.price" type="number"
                                    style="width:90px;padding:5px;border:1.5px solid #f97316;border-radius:6px;font-size:13px;text-align:center;outline:none"/>
                            </template>
                            <span v-else style="font-size:13px;font-weight:500;color:#1e293b">{{ formatPrice(product.price) }}</span>
                        </td>
                        <td style="padding:12px 16px;text-align:center">
                            <template v-if="editingId === product.id">
                                <input v-model="editForm.stock" type="number" min="0"
                                    style="width:70px;padding:5px;border:1.5px solid #f97316;border-radius:6px;font-size:13px;text-align:center;outline:none"/>
                            </template>
                            <span v-else :style="`font-size:14px;font-weight:700;${product.stock === 0 ? 'color:#dc2626' : product.stock <= 5 ? 'color:#c2410c' : 'color:#15803d'}`">
                                {{ product.stock }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center">
                            <template v-if="editingId === product.id">
                                <select v-model="editForm.is_available"
                                    style="padding:5px;border:1.5px solid #f97316;border-radius:6px;font-size:12px;outline:none;background:white">
                                    <option :value="true">Disponible</option>
                                    <option :value="false">No disponible</option>
                                </select>
                            </template>
                            <span v-else :style="`font-size:12px;padding:3px 10px;border-radius:20px;font-weight:500;${product.is_available ? 'background:#f0fdf4;color:#15803d' : 'background:#fef2f2;color:#dc2626'}`">
                                {{ product.is_available ? 'Disponible' : 'Agotado' }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center">
                            <div style="display:flex;gap:6px;justify-content:center">
                                <template v-if="editingId === product.id">
                                    <button @click="saveEdit(product.id)"
                                        style="padding:5px 12px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:6px;font-size:12px;cursor:pointer;font-weight:500">
                                        Guardar
                                    </button>
                                    <button @click="editingId=null;editPreview=null"
                                        style="padding:5px 12px;background:#f1f5f9;color:#64748b;border:none;border-radius:6px;font-size:12px;cursor:pointer">
                                        Cancelar
                                    </button>
                                </template>
                                <template v-else>
                                    <button @click="startEdit(product)"
                                        style="padding:5px 12px;background:#fff7ed;color:#f97316;border:1px solid #fed7aa;border-radius:6px;font-size:12px;cursor:pointer;font-weight:500">
                                        Editar
                                    </button>
                                    <button @click="deleteProduct(product.id)"
                                        style="padding:5px 12px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:6px;font-size:12px;cursor:pointer;font-weight:500">
                                        Eliminar
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>
</template>
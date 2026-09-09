<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN:       Conecta la tabla `payments` con `orders`
 * POR QUÉ:         Hoy en día un Payment se crea sin relación a ningún
 *                  pedido; esto permite que el monto a cobrar se calcule
 *                  siempre desde Order::total (fuente confiable), en vez
 *                  de confiar en un valor enviado desde el formulario.
 * AFECTA A:        app/Models/Payment.php (nueva relación order())
 *                  app/Models/Order.php   (nueva relación payment())
 *                  app/Http/Controllers/PaymentController.php (próximo paso)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Nullable: los pagos ya existentes sin pedido asociado no se rompen.
            $table->foreignId('order_id')
                ->nullable()
                ->after('user_id')
                ->constrained('orders')   // crea la llave foránea hacia la tabla orders
                ->nullOnDelete();          // si se borra el pedido, el pago queda con order_id = null (no se borra el pago)
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
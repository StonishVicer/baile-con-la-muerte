<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const today = new Date().toISOString().split('T')[0];

const selectedDate = ref('');
const dateError = ref('');
const slots = ref<{ time: string; available: boolean }[]>([]);
const loading = ref(false);
const selectedTime = ref<string | null>(null);
const successMessage = ref('');

const form = reactive({ name: '', email: '' });
const formErrors = reactive<Record<string, string>>({});
const submitting = ref(false);

function isWeekday(date: string): boolean {
    const d = new Date(date + 'T00:00:00');

    return d.getDay() !== 0 && d.getDay() !== 6;
}

function onDateChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const date = target.value;

    selectedDate.value = date;
    dateError.value = '';
    slots.value = [];
    selectedTime.value = null;
    successMessage.value = '';

    if (!date) {
        return;
    }

    if (date < today) {
        dateError.value = 'No puedes seleccionar una fecha pasada.';

        return;
    }

    if (!isWeekday(date)) {
        dateError.value = 'Solo dias habiles, de lunes a viernes.';

        return;
    }

    fetchSlots(date);
}

async function fetchSlots(date: string) {
    loading.value = true;

    try {
        const response = await fetch(`/api/available-slots/${date}`);
        const data = await response.json();

        if (!response.ok) {
            dateError.value = data.error ?? 'Error al cargar los horarios.';

            return;
        }

        slots.value = data.slots ?? [];
    } catch {
        dateError.value = 'Error de conexion al cargar los horarios.';
    } finally {
        loading.value = false;
    }
}

function selectSlot(slot: { time: string; available: boolean }) {
    if (!slot.available) {
        return;
    }

    selectedTime.value = slot.time;
    formErrors.name = '';
    formErrors.email = '';
    formErrors.general = '';
    successMessage.value = '';
}

function slotLabel(time: string): string {
    const [h] = time.split(':');
    const end = String(parseInt(h) + 1).padStart(2, '0');

    return `${time} \u2013 ${end}:00`;
}

async function submitAppointment() {
    formErrors.name = '';
    formErrors.email = '';
    formErrors.general = '';

    let hasError = false;

    if (!form.name.trim()) {
        formErrors.name = 'El nombre es obligatorio.';
        hasError = true;
    }

    if (!form.email.trim()) {
        formErrors.email = 'El correo es obligatorio.';
        hasError = true;
    }

    if (hasError) {
        return;
    }

    submitting.value = true;

    try {
        const response = await fetch('/api/appointments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                name: form.name,
                email: form.email,
                date: selectedDate.value,
                time: selectedTime.value,
            }),
        });
        const data = await response.json();

        if (!response.ok) {
            formErrors.general = data.error ?? 'Error al reservar la cita.';

            return;
        }

        successMessage.value = `Cita reservada para el ${selectedDate.value} a las ${selectedTime.value}.`;
        form.name = '';
        form.email = '';
        selectedTime.value = null;

        await fetchSlots(selectedDate.value);
    } catch {
        formErrors.general = 'Error de conexion. Intentalo de nuevo.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <Head title="Reservar cita" />

    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:py-16">
        <div class="mb-10">
            <h1 class="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">
                Reserva tu cita con la muerte
            </h1>
            <p class="mt-2 text-sm text-muted-foreground">
                Elige tu momento eterno. Selecciona una fecha, elige un horario disponible y confirma tus datos.
            </p>
        </div>

        <div class="space-y-8">
            <div class="rounded-xl border bg-card p-5">
                <label
                    for="date"
                    class="mb-2 block text-sm font-medium text-foreground"
                >
                    Fecha
                </label>
                <input
                    id="date"
                    type="date"
                    :min="today"
                    :value="selectedDate"
                    @change="onDateChange"
                    class="h-10 w-full max-w-60 rounded-md border border-input bg-background px-3 text-sm shadow-sm transition-colors focus:border-ring focus:outline-none focus:ring-[3px] focus:ring-ring/50 sm:text-sm [color-scheme:light] dark:[color-scheme:dark]"
                />
                <p
                    v-if="dateError"
                    class="mt-2 text-sm font-medium text-destructive"
                >
                    {{ dateError }}
                </p>
                <p v-else class="mt-2 text-xs text-muted-foreground">
                    Dias habiles. Horario de 09:00 a 19:00, bloques de una hora.
                </p>
            </div>

            <div v-if="loading" class="rounded-xl border bg-card p-5">
                <p class="text-sm text-muted-foreground">Cargando horarios…</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <div
                        v-for="n in 10"
                        :key="n"
                        class="h-10 w-24 animate-pulse rounded-lg bg-muted"
                    />
                </div>
            </div>

            <div
                v-else-if="slots.length > 0"
                class="rounded-xl border bg-card p-5"
            >
                <h2 class="mb-3 text-sm font-medium text-foreground">
                    Horarios para el {{ selectedDate }}
                </h2>

                <div class="flex flex-wrap gap-2.5">
                    <button
                        v-for="slot in slots"
                        :key="slot.time"
                        type="button"
                        :disabled="!slot.available"
                        @click="selectSlot(slot)"
                        :class="[
                            'rounded-lg border px-4 py-2.5 text-sm font-medium transition-colors',
                            slot.available
                                ? selectedTime === slot.time
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-border bg-background hover:border-primary/60 hover:bg-accent'
                                : 'cursor-not-allowed border-transparent bg-muted text-muted-foreground line-through',
                        ]"
                    >
                        {{ slotLabel(slot.time) }}
                    </button>
                </div>

                <p
                    v-if="!slots.some(function(s) { return s.available; })"
                    class="mt-3 text-sm text-muted-foreground"
                >
                    No quedan horarios libres para esta fecha.
                </p>
            </div>

            <form
                v-if="selectedTime"
                @submit.prevent="submitAppointment"
                class="rounded-xl border bg-card p-5"
                novalidate
            >
                <h2 class="mb-3 text-sm font-medium text-foreground">
                    Completa tus datos
                </h2>
                <p class="mb-4 text-sm text-muted-foreground">
                    Horario: {{ slotLabel(selectedTime) }}
                </p>

                <div class="space-y-4">
                    <div>
                        <label
                            for="name"
                            class="mb-1.5 block text-sm font-medium text-foreground"
                        >
                            Nombre completo
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Tu nombre"
                            class="h-10 w-full max-w-sm rounded-md border border-input bg-background px-3 text-sm shadow-sm transition-colors focus:border-ring focus:outline-none focus:ring-[3px] focus:ring-ring/50"
                        />
                        <p
                            v-if="formErrors.name"
                            class="mt-1.5 text-sm font-medium text-destructive"
                        >
                            {{ formErrors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="email"
                            class="mb-1.5 block text-sm font-medium text-foreground"
                        >
                            Correo electronico
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="tu@correo.com"
                            class="h-10 w-full max-w-sm rounded-md border border-input bg-background px-3 text-sm shadow-sm transition-colors focus:border-ring focus:outline-none focus:ring-[3px] focus:ring-ring/50"
                        />
                        <p
                            v-if="formErrors.email"
                            class="mt-1.5 text-sm font-medium text-destructive"
                        >
                            {{ formErrors.email }}
                        </p>
                    </div>

                    <p
                        v-if="formErrors.general"
                        class="text-sm font-medium text-destructive"
                    >
                        {{ formErrors.general }}
                    </p>

                    <button
                        type="submit"
                        :disabled="submitting"
                        class="inline-flex h-10 items-center justify-center rounded-md bg-primary px-6 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{ submitting ? 'Reservando\u2026' : 'Confirmar reserva' }}
                    </button>
                </div>
            </form>

            <div
                v-if="successMessage"
                class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950"
            >
                <p class="text-sm font-medium text-green-700 dark:text-green-400">
                    {{ successMessage }}
                </p>
                <p class="mt-1 text-xs text-green-600 dark:text-green-500">
                    Puedes seleccionar otra fecha para reservar una nueva cita.
                </p>
            </div>
        </div>
    </div>
</template>

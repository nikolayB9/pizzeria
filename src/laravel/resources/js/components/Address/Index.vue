<script>
import axios from "axios";

export default {
    name: "Index",

    data() {
        return {
            addresses: [],
            defaultAddressId: null,
            selectedAddressId: null,
            isUpdatingDefault: false,
            showCreateModal: false,
            newAddress: {
                city_id: null,
                street_id: null,
                house: null,
                entrance: null,
                floor: null,
                flat: null,
                intercom_code: null
            },
            cities: [],
            streets: [],
            showEditModal: false,
            editAddress: {},
        }
    },

    mounted() {
        this.getAddresses()
    },

    watch: {
        $route(to, from) {

        },
    },

    computed: {
        fromCheckout() {
            return this.$route.query.fromCheckout === '1';
        }
    },

    methods: {
        getAddresses() {
            axios.get('/api/v1/addresses')
                .then(res => {
                    this.addresses = res.data.data;
                    const def = this.addresses.find(a => a.is_default);
                    this.defaultAddressId = def ? def.id : null;
                    this.selectedAddressId = this.defaultAddressId;
                });
        },
        getEditAddress(id) {
            return axios.get(`/api/v1/addresses/${id}`)
                .then(res => {
                    this.editAddress = res.data.data
                })
                .catch(error => {
                    const message = error.response?.data?.message || error.message || 'Неизвестная ошибка'
                    alert(message)
                })
        },
        submitCreateAddress() {
            axios.post('/api/v1/addresses', {
                city_id: this.newAddress.city_id,
                street_id: this.newAddress.street_id,
                house: this.newAddress.house,
                entrance: this.newAddress.entrance,
                floor: this.newAddress.floor,
                flat: this.newAddress.flat,
                intercom_code: this.newAddress.intercom_code
            })
                .then(res => {
                    this.getAddresses()
                    this.closeCreateModal()
                })
                .catch(error => {
                    const message = error.response?.data?.message || error.message || 'Неизвестная ошибка'
                    alert(message)
                })

            this.resetFormNewAddress()
            this.closeCreateModal()
        },
        submitEditAddress() {
            const id = this.editAddress.id
            axios.patch(`/api/v1/addresses/${id}`, this.editAddress)
                .then(() => {
                    this.getAddresses()
                    this.closeEditModal()
                })
                .catch(error => {
                    alert(error.response?.data?.message ?? 'Ошибка при обновлении адреса')
                })
        },
        changeDefaultAddress(newAddressId) {
            if (this.isUpdatingDefault || newAddressId === this.defaultAddressId) return;

            // временно сохраняем старый
            const previousId = this.defaultAddressId;
            this.isUpdatingDefault = true;

            axios.patch(`/api/v1/addresses/${newAddressId}/default`)
                .then(() => {
                    this.defaultAddressId = newAddressId;
                })
                .catch(() => {
                    alert('Не удалось изменить адрес доставки')
                    this.selectedAddressId = previousId;
                })
                .finally(() => {
                    this.isUpdatingDefault = false;
                });
        },
        deleteAddress(id) {
            if (!confirm('Удалить адрес?')) return

            axios.delete(`/api/v1/addresses/${id}`)
                .then(() => {
                    this.getAddresses()
                    this.closeEditModal()
                })
                .catch(error => {
                    alert(error.response?.data?.message ?? 'Ошибка при удалении адреса')
                })
        },
        openCreateModal() {
            if (!this.cities.length) {
                this.getCities()
            }
            this.showCreateModal = true
        },
        closeCreateModal() {
            this.showCreateModal = false
            this.resetFormNewAddress()
        },
        resetFormNewAddress() {
            this.newAddress = {
                city_id: null,
                street_id: null,
                house: null,
                entrance: null,
                floor: null,
                flat: null,
                intercom_code: null
            }
            this.streets = []
        },
        getCities() {
            axios.get('/api/v1/cities')
                .then(res => {
                    this.cities = res.data.data
                })
        },
        getStreets(cityId) {
            axios.get(`/api/v1/cities/${cityId}/streets`)
                .then(res => {
                    this.streets = res.data.data
                })
        },
        onCityChange() {
            this.newAddress.street_id = '' // сброс улицы
            if (this.newAddress.city_id) {
                this.getStreets(this.newAddress.city_id)
            }
        },
        openEditModal(addressId) {
            this.getEditAddress(addressId)
                .then(() => {
                    if (!this.cities.length) {
                        this.getCities()
                    }

                    this.getStreets(this.editAddress.city_id)

                    this.showEditModal = true
                })
        },
        closeEditModal() {
            this.editAddress = {}
            this.showEditModal = false
        },
    }

}
</script>

<template>
    <h1>Адреса доставки</h1>

    <div v-if="addresses.length > 0">
        <div v-for="address in addresses" :key="address.id">
            <label :style="{ opacity: isUpdatingDefault ? 0.6 : 1 }">
                <input
                    type="radio"
                    :value="address.id"
                    v-model="selectedAddressId"
                    :disabled="isUpdatingDefault"
                    @change="changeDefaultAddress(address.id)"
                />
                {{ address.city }}, {{ address.street }}, {{ address.house }}
            </label>

            <!-- Кнопка редактирования -->
            <button @click="openEditModal(address.id)">✏️</button>
        </div>
    </div>

    <div>
        <button @click="openCreateModal" class="btn btn-primary">
            + Новый адрес
        </button>
    </div>

    <div v-if="fromCheckout">
        <router-link
            :to="{ name: 'checkout.index' }"
            class="checkout-button"
        >
            Вернуться к оформлению заказа
        </router-link>
    </div>

    <!-- Модальное окно "Новый адрес" -->
    <div v-if="showCreateModal" class="modal-overlay">
        <div class="modal">
            <h2>Новый адрес</h2>
            <form @submit.prevent="submitCreateAddress">
                <label>
                    Город
                    <select v-model="newAddress.city_id" @change="onCityChange">
                        <option disabled value="">Выберите город</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">
                            {{ city.name }}
                        </option>
                    </select>
                </label>

                <label>
                    Улица
                    <select v-model="newAddress.street_id" :disabled="!streets.length">
                        <option disabled value="">Выберите улицу</option>
                        <option v-for="street in streets" :key="street.id" :value="street.id">
                            {{ street.name }}
                        </option>
                    </select>
                </label>

                <label>
                    Дом
                    <input v-model="newAddress.house" placeholder="Дом">
                </label>

                <label>
                    Подъезд
                    <input v-model="newAddress.entrance" placeholder="Подъезд">
                </label>

                <label>
                    Этаж
                    <input v-model="newAddress.floor" placeholder="Этаж">
                </label>

                <label>
                    Квартира
                    <input v-model="newAddress.flat" placeholder="Квартира">
                </label>

                <label>
                    Код домофона
                    <input v-model="newAddress.intercom_code" placeholder="Код домофона">
                </label>

                <div class="actions">
                    <button type="submit">Сохранить</button>
                    <button type="button" @click="closeCreateModal">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Модальное окно "Редактировать адрес" -->
    <div v-if="showEditModal" class="modal-overlay">
        <div class="modal">
            <form @submit.prevent="submitEditAddress">
                <label>
                    Город
                    <select v-model="editAddress.city_id" @change="getStreets(editAddress.city_id)">
                        <option value="">Выберите город</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">
                            {{ city.name }}
                        </option>
                    </select>
                </label>

                <label>
                    Улица
                    <select v-model="editAddress.street_id">
                        <option value="">Выберите улицу</option>
                        <option v-for="street in streets" :key="street.id" :value="street.id">
                            {{ street.name }}
                        </option>
                    </select>
                </label>

                <label>
                    Дом
                    <input required v-model="editAddress.house" placeholder="Дом">
                </label>

                <label>
                    Подъезд
                    <input v-model="editAddress.entrance" placeholder="Подъезд">
                </label>

                <label>
                    Этаж
                    <input v-model="editAddress.floor" placeholder="Этаж">
                </label>

                <label>
                    Квартира
                    <input v-model="editAddress.flat" placeholder="Квартира">
                </label>

                <label>
                    Код домофона
                    <input v-model="editAddress.intercom_code" placeholder="Код домофона">
                </label>

                <div class="actions">
                    <button type="submit">Сохранить</button>
                    <button type="button" @click="deleteAddress(editAddress.id)">Удалить</button>
                    <button type="button" @click="closeEditModal">Отмена</button>
                </div>
            </form>
        </div>
    </div>

</template>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1rem; /* чтобы модалка не упиралась в края экрана */
    overflow: auto; /* добавляет прокрутку если нужно */
}

.modal {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    min-width: 300px;
    max-height: 90vh; /* ограничим высоту */
    overflow-y: auto; /* и добавим вертикальную прокрутку */
    width: 100%;
    max-width: 500px; /* чтобы не растягивалась на весь экран */
}

.actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
}

/* Основной заголовок */
h1 {
    font-size: 1.8rem;
    margin-bottom: 1rem;
}

/* Блок адреса */
label {
    display: inline-block;
    margin-bottom: 0.5rem;
    font-size: 1rem;
    cursor: pointer;
}

/* Радио-кнопка + текст */
input[type="radio"] {
    margin-right: 0.5rem;
    transform: scale(1.2);
    cursor: pointer;
}

/* Кнопка редактирования (✏️) */
button {
    cursor: pointer;
    background: none;
    border: none;
    font-size: 1rem;
}

/* Кнопка "Новый адрес" */
.btn.btn-primary {
    margin-top: 1rem;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn.btn-primary:hover {
    background-color: #0056b3;
}

.checkout-button {
    display: inline-block;
    padding: 0.6rem 1rem;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-size: 1rem;
    margin-top: 1rem;
}

.checkout-button:hover {
    background-color: #218838;
}

/* Стили для форм */
form select,
form input {
    display: block;
    width: 100%;
    padding: 0.5rem;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 4px;
}

/* Кнопки в actions */
.actions button {
    padding: 0.5rem 0.8rem;
    font-size: 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

/* Стили для кнопок внутри actions */
.actions button[type="submit"] {
    background-color: #28a745;
    color: white;
}

.actions button[type="submit"]:hover {
    background-color: #218838;
}

.actions button[type="button"] {
    background-color: #6c757d;
    color: white;
}

.actions button[type="button"]:hover {
    background-color: #5a6268;
}

/* Кнопка удаления */
.actions button[type="button"]:nth-child(2) {
    background-color: #dc3545;
}

.actions button[type="button"]:nth-child(2):hover {
    background-color: #c82333;
}

.modal form label {
    display: block;
    font-size: 0.875rem; /* 14px */
    color: #555;
    margin-bottom: 1rem;
}

.modal form label input,
.modal form label select {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    padding: 0.4rem;
}
</style>


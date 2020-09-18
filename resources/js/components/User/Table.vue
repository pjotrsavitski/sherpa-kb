<template>
    <div>
        <user-create :role-options="roleOptions"></user-create>
        <user-edit :user="user" :role-options="roleOptions" v-if="user"></user-edit>

        <h3>Users</h3>

        <b-button
            v-b-modal="'user-create'"
            variant="primary"
            class="my-4"
            v-b-tooltip.right="'Add new user'"
        >
            <font-awesome-icon :icon="['fas', 'user-plus']" size="lg" />
        </b-button>
        
        <b-table
            striped
            hover
            :fields="fields"
            :items="items"
            primary-key="id"
            thead-class="text-center"
            stacked="lg"
            :per-page="perPage"
            :current-page="currentPage"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            :busy="isBusy"
        >
            <template v-slot:cell(id)="data">
                {{ data.value }}
            </template>

            <template v-slot:cell(name)="data">
                {{ data.value }}
            </template>

            <template v-slot:cell(email)="data">
                <a :href="formatMailto(data.value)">
                    {{ data.value }}
                </a>
            </template>

            <template v-slot:cell(email_verified_at)="data">
                <b-button
                    pill
                    variant="outline-success"
                    v-b-tooltip
                    :title="formatDate(data.value)"
                    v-if="!!data.value"
                >
                    <font-awesome-icon :icon="['fas', 'user-check']" size="lg" />
                </b-button>
                <b-button
                    pill
                    variant="outline-secondary"
                    disabled
                    v-if="!data.value"
                >
                    <font-awesome-icon :icon="['fas', 'user-check']" size="lg" />
                </b-button>
            </template>

            
            <template v-slot:cell(created_at)="data">
                {{ formatDate(data.value) }}
            </template>

            <template v-slot:cell(roles)="data">
                <b-badge v-for="role in data.value" v-bind:key="role.id" pill>{{ capitalize(role.name) }}</b-badge>
            </template>

            <template v-slot:cell(actions)="data">
                <b-button-group>
                    <b-button
                        variant="success"
                        @click="onEditUser(data.item)"
                        v-b-tooltip
                        title="Edit user"
                    >
                        <font-awesome-icon :icon="['fas', 'user-edit']" size="lg" />
                    </b-button>
                    <b-button
                        variant="danger"
                        @click="onDeleteUser(data.item)"
                        v-b-tooltip
                        title="Delete user"
                        :disabled="data.item.id === currentUser.id"
                    >
                        <font-awesome-icon :icon="['fas', 'user-minus']" size="lg" />
                    </b-button>
                </b-button-group>
            </template>
        </b-table>

        <b-pagination
          v-model="currentPage"
          :total-rows="totalRows"
          :per-page="perPage"
          align="center"
          v-if="totalRows > perPage"
        ></b-pagination>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import UserCreate from './Create.vue'
    import UserEdit from './Edit.vue'
    import ToastHelpers from '../../mixins/ToastHelpers'
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faUserCheck, faUserEdit, faUserPlus, faUserMinus } from '@fortawesome/free-solid-svg-icons'

    library.add(faUserCheck)
    library.add(faUserEdit)
    library.add(faUserPlus)
    library.add(faUserMinus)

    export default {
        components: {
            UserCreate,
            UserEdit
        },
        mixins: [ToastHelpers],
        computed: {
            ...mapState({
                perPage: state => state.app.itemsPerPage,
                currentUser: state => state.app.user
            }),
            totalRows() {
                return this.items.length
            },
            fields() {
                const fields = [
                    {
                        key: 'id',
                        label: 'ID',
                        sortable: true,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'name',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'email',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'email_verified_at',
                        label: 'Verified',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'created_at',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                                       {
                        key: 'roles',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'actions',
                        label: '',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    }
                ]

                return fields
            },
            roleOptions() {
                return this.roles.map(role => {
                    return {
                        value: role.id,
                        text: this.capitalize(role.name)
                    }
                })
            }
        },
        data() {
            return {
                currentPage: 1,
                sortBy: 'id',
                sortDesc: true,
                items: [],
                isBusy: true,
                user: null,
                roles: []
            }
        },
        methods: {
            formatDate(dateString) {
                const date = new Date(dateString)
                
                return `${date.getDate().toString().padStart(2, '0')}.${(date.getMonth() + 1).toString().padStart(2, '0')}.${date.getFullYear()} ${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`
            },
            formatMailto(emailString) {
                return `mailto:${emailString}`
            },
            onEditUser(user) {
                this.user = user
                this.$nextTick(() => {
                    this.$bvModal.show('user-edit')
                })
            },
            capitalize(string) {
                return string.charAt(0).toUpperCase() + string.slice(1)
            },
            onDeleteUser(user) {
                this.$bvModal.msgBoxConfirm(`Please confirm that you really want to delete user ${user.name}.`, {
                    title: 'Please Confirm',
                    size: 'sm',
                    buttonSize: 'sm',
                    okVariant: 'danger',
                    okTitle: 'YES',
                    cancelTitle: 'NO',
                    footerClass: 'p-2',
                    hideHeaderClose: false,
                    centered: true
                })
                .then(value => {
                    if (value) {
                        this.isBusy = true
                        axios.delete(`/users/${user.id}`)
                        .then(response => {
                            this.isBusy = false

                            const index = this.items.findIndex(item => {
                                return item.id === user.id
                            })

                            if (index !== -1) {
                                this.items.splice(index, 1)
                            }
                        })
                        .catch(error => {
                            this.isBusy = false
                            console.error('User delete:', error)

                            this.displayHttpError(error)
                        })
                    }
                })
                .catch(err => {
                    console.log('User delete confirmation error:', err)
                })
            }
        },
        created() {
            axios.get('/users')
            .then(response => {
                this.isBusy = false
                this.items = response.data.data
            })
            .catch(error => {
                this.isBusy = false
                console.error('Users loading:', error)
            })

            axios.get('/users/roles')
            .then(response => {
                this.isBusy = false
                this.roles = response.data.data
            })
            .catch(error => {
                this.isBusy = false
                console.error('Roles loading:', error)
            })

            this.$root.$on('userCreated', user => {
                this.items.push(user)
            })

            this.$root.$on('userUpdated', user => {
                const index = this.items.findIndex(item => {
                    return item.id === user.id
                })
                
                if (index !== -1) {
                    Vue.set(this.items, index, user)
                } else {
                    console.warn('Index not found, could not update a user:', user)
                }
            })
        }
    }
</script>

<style scoped>
.badge:not(:last-child) {
    margin-right: 5px;
}
</style>

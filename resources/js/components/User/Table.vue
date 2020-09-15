<template>
    <div>
        <user-edit :user="user" v-if="user"></user-edit>
        <h3>Users</h3>
        
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
                <b-button
                    variant="success"
                    @click="onEditUser(data.item)"
                >
                    <font-awesome-icon :icon="['fas', 'user-edit']" size="lg" />
                </b-button>
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
    import UserEdit from './Edit.vue'
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faUserCheck, faUserEdit } from '@fortawesome/free-solid-svg-icons'

    library.add(faUserCheck)
    library.add(faUserEdit)

    export default {
        components: {
            UserEdit
        },
        computed: {
            ...mapState({
                perPage: state => state.app.itemsPerPage
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
            }
        },
        data() {
            return {
                currentPage: 1,
                sortBy: 'id',
                sortDesc: true,
                items: [],
                isBusy: true,
                user: null
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
            }
        },
        created() {
            axios.get('/users')
            .then(response => {
                this.isBusy = false
                this.items = response.data
            })
            .catch(error => {
                this.isBusy = false
                console.error('Users loading:', error)
            })
        }
    }
</script>

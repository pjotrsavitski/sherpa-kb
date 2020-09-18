<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Edit user"
            ok-title="Save"
            @show="resetModal"
            @hidden="resetModal"
            @ok="handleSave"
            :ok-disabled="!canSave()"
            :busy="isBusy"
        >
            <form
                ref="form"
                @submit.stop.prevent="handleSubmit"
            >
                <b-form-group
                    id="input-group-name"
                    label="Name"
                    label-for="input-name"
                    invalid-feedback="Name is required"
                    :state="nameState"
                >
                    <b-form-input
                        id="input-name"
                        v-model="form.name"
                        type="text"
                        required
                        :state="nameState"
                        trim
                        :disabled="!canEdit()"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-email"
                    label="Email"
                    label-for="input-emai"
                    invalid-feedback="Email is required"
                    :state="emailState"
                >
                    <b-form-input
                        id="input-email"
                        v-model="form.email"
                        type="email"
                        required
                        :state="emailState"
                        trim
                        :disabled="!canEdit()"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-password"
                    label="Password"
                    label-for="input-password"
                    invalid-feedback="Length should be at least 8 characters."
                    :state="passwordState"
                >
                    <b-form-input
                        id="input-password"
                        v-model="form.password"
                        type="password"
                        trim
                        :disabled="!canEdit()"
                        :state="passwordState"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-confirmation"
                    label="Password confirmation"
                    label-for="input-confirmation"
                    invalid-feedback="Length should be at least 8 characters. Password and confirmation should match."
                    :state="confirmationState"
                >
                    <b-form-input
                        id="input-confirmation"
                        v-model="form.confirmation"
                        type="password"
                        trim
                        :disabled="!canEdit()"
                        :state="confirmationState"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-roles"
                    label="Roles"
                    label-for="input-roles"
                >
                    <b-form-select
                        id="input-roles"
                        v-model="form.roles"
                        :options="roleOptions"
                        multiple
                    ></b-form-select>
                </b-form-group>
            </form>
        </b-modal>
</template>

<script>
    import ToastHelpers from '../../mixins/ToastHelpers'

    export default {
        props: ['user', 'roleOptions'],
        mixins: [ToastHelpers],
        computed: {
            modalId() {
                return 'user-edit'
            },
            nameState() {
                return (this.form.name && this.form.name.length) > 0 ? true : false
            },
            emailState() {
                return (this.form.email && this.form.email.length) > 0 ? true : false
            },
            passwordState() {
                if (this.form.password === '') {
                    return null
                }

                return (this.form.password && this.form.password.length >= 8) ? true : false
            },
            confirmationState() {
                if (this.form.confirmation === '') {
                    return null
                }

                return (this.form.confirmation && this.form.confirmation.length >= 8 && this.form.password === this.form.confirmation) ? true : false
            }
        },
        data() {
            return {
                form: {
                    name: "",
                    email: "",
                    password: "",
                    confirmation: "",
                    roles: []
                },
                isBusy: false
            };
        },
        methods: {
            resetModal() {
                this.form.name = this.user.name
                this.form.email = this.user.email
                this.form.password = ""
                this.form.confirmation = ""
                this.form.roles = this.user.roles.map(role => {
                    return role.id
                })
            },
            canEdit() {
                return true
            },
            canSave() {
                return this.canEdit() && this.nameState && this.emailState && (this.passwordState || this.passwordState === null) && (this.confirmationState || this.confirmationState === null)
            },
            handleSave(bvModelEvent) {
                bvModelEvent.preventDefault()
                this.handleSubmit()
            },
            handleSubmit() {
                if (!this.$refs.form.checkValidity()) {
                    return
                }

                this.isBusy = true
                const data = {
                    name: this.form.name,
                    roles: this.form.roles
                }

                if (this.user.email !== this.form.email) {
                    data.email = this.form.email
                }

                if (this.form.password && this.form.confirmation) {
                    data.password = this.form.password
                    data.password_confirmation = this.form.confirmation
                }

                axios.put('/users/' + this.user.id, data)
                .then(response => {
                    this.isBusy = false
                    this.$root.$emit('userUpdated', response.data.data)

                    this.$nextTick(() => {
                        this.$bvModal.hide(this.modalId)
                    });
                })
                .catch(error => {
                    this.isBusy = false
                    console.error(error)

                    this.displayHttpError(error)
                })
            }
        }
    }
</script>

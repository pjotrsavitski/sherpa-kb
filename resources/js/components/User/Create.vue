<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Create user"
            ok-title="Create"
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
                        @update="setDirty('name')"
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
                        @update="setDirty('email')"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-password"
                    label="Password"
                    label-for="input-password"
                    invalid-feedback="Password is required. Length should be at least 8 characters."
                    :state="passwordState"
                >
                    <b-form-input
                        id="input-password"
                        v-model="form.password"
                        type="password"
                        trim
                        required
                        :state="passwordState"
                        :disabled="!canEdit()"
                        @update="setDirty('password')"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-confirmation"
                    label="Password confirmation"
                    label-for="input-confirmation"
                    invalid-feedback="Confirmation is required. Length should be at least 8 characters. Password and confirmation should match."
                    :state="confirmationState"
                >
                    <b-form-input
                        id="input-confirmation"
                        v-model="form.confirmation"
                        type="password"
                        trim
                        required
                        :state="confirmationState"
                        :disabled="!canEdit()"
                        @update="setDirty('confirmation')"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-language"
                    label="Language"
                    label-for="input-language"
                >
                    <b-form-select
                        v-model="form.language"
                        :options="languageOptions"
                        id="input-language"
                    ></b-form-select>
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
        props: ['languageOptions', 'roleOptions'],
        mixins: [ToastHelpers],
        computed: {
            modalId() {
                return 'user-create'
            },
            nameState() {
                if (this.form.dirty.indexOf('name') === -1) {
                    return null;
                }

                return (this.form.name && this.form.name.length) > 0 ? true : false
            },
            emailState() {
                if (this.form.dirty.indexOf('email') === -1) {
                    return null;
                }

                return (this.form.email && this.form.email.length) > 0 ? true : false
            },
            passwordState() {
                if (this.form.dirty.indexOf('password') === -1) {
                    return null;
                }

                return (this.form.password && this.form.password.length >= 8) ? true : false
            },
            confirmationState() {
                if (this.form.dirty.indexOf('confirmation') === -1) {
                    return null;
                }

                return (this.form.confirmation && this.form.confirmation.length >= 8 && this.form.password === this.form.confirmation) ? true : false
            }
        },
        data() {
            return {
                form: {
                    dirty: [],
                    name: "",
                    email: "",
                    password: "",
                    confirmation: "",
                    language: null,
                    roles: []
                },
                isBusy: false
            };
        },
        methods: {
            resetModal() {
                this.form.dirty = []
                this.form.name = ""
                this.form.email = ""
                this.form.password = ""
                this.form.confirmation = ""
                this.form.language = this.languageOptions[0].value
                this.form.roles = []
            },
            canEdit() {
                return true
            },
            canSave() {
                return this.canEdit() && this.nameState && this.emailState && this.passwordState && this.confirmationState
            },
            handleSave(bvModelEvent) {
                bvModelEvent.preventDefault()
                this.handleSubmit()
            },
            handleSubmit() {
                if (!this.$refs.form.checkValidity()) {
                    return
                }

                this.isBusy = true;
                axios.post('/users', {
                    name: this.form.name,
                    email: this.form.email,
                    password: this.form.password,
                    password_confirmation: this.form.confirmation,
                    language: this.form.language,
                    roles: this.form.roles
                })
                .then(response => {
                    this.isBusy = false;
                    this.$root.$emit('userCreated', response.data.data)

                    this.$nextTick(() => {
                        this.$bvModal.hide(this.modalId)
                    });
                })
                .catch(error => {
                    this.isBusy = false
                    console.error(error)

                    this.displayHttpError(error)
                })
            },
            setDirty(field) {
                if (this.form.dirty.indexOf(field) === -1) {
                    this.form.dirty.push(field)
                }
            }
        }
    }
</script>

<template>
    <div class="float-right">
        <b-button
            v-b-tooltip
            title="Reload all application data"
            variant="outline-secondary"
            size="sm"
            @click="onReload()"
            v-if="!isActive"
            :disabled="isBusy"
        >
            <font-awesome-icon :icon="['fas', 'sync']" v-if="!isBusy"/>
            <b-spinner
                label="Loading"
                type="grow"
                variant="secondary"
                :small="true"
                v-if="isBusy"
            ></b-spinner>
        </b-button>
        <b-button-group
            size="sm"
            v-if="isActive"
        >
            <b-button
                title="Live-updates are enabled"
                v-b-tooltip
                variant="light"
                :class="stateClass"
            >
                <font-awesome-icon :icon="['fas', 'globe']" />
            </b-button>
            <b-button
                title="Current state of live-updates service connection"
                v-b-tooltip
                variant="light"
                size="sm"
                :class="stateClass"
            >
                <span class="text-capitalize">{{ connectionState }}</span>
            </b-button>
            <b-button
                v-b-tooltip
                title="Reload all application data"
                variant="light"
                @click="onReload()"
                v-if="showReload"
                :disabled="isBusy"
            >
                <font-awesome-icon :icon="['fas', 'sync']" v-if="!isBusy"/>
                <b-spinner
                    label="Loading"
                    type="grow"
                    variant="secondary"
                    :small="true"
                    v-if="isBusy"
                ></b-spinner>
            </b-button>
        </b-button-group>
    </div>
</template>

<script>
import { library } from '@fortawesome/fontawesome-svg-core'
import { faSync, faGlobe } from '@fortawesome/free-solid-svg-icons'

library.add(faSync, faGlobe)

export default {
    name: "AppSync",
    props: ['isActive', 'connectionState'],
    data() {
        return {
            isBusy: false,
            loadDataWhenConnected: false
        }
    },
    watch: {
        connectionState(newState, oldState) {
            if (newState === 'connected' && this.loadDataWhenConnected) {
                this.loadData()
                this.loadDataWhenConnected = false
            }

            if (newState === 'unavailable' || newState === 'failed' || (newState === 'connecting' && oldState === 'connected')) {
                this.loadDataWhenConnected = true
            }
        }
    },
    computed: {
        stateClass() {
            let className

            switch(this.connectionState) {
                case 'initialized':
                    className = 'text-info'
                    break;
                case 'connecting':
                    className = 'text-secondary'
                    break;
                case 'connected':
                    className = 'text-success'
                    break;
                case 'unavailable':
                case 'failed':
                    className = 'text-danger'
                    break;
                case 'disconnected':
                    className = 'text-muted'
                    break;
                default:
                    className = 'text-secondary'
            }

            return className
        },
        showReload() {
            return (this.isActive && (this.connectionState === 'unavailable' || this.connectionState === 'failed' || this.connectionState === 'disconnected')) ? true : false;
        }
    },
    methods: {
        loadData() {
            return Promise.allSettled([
                this.$store.dispatch('answers/loadAllAnswers'),
                this.$store.dispatch('questions/loadAllQuestions'),
                this.$store.dispatch('pendingQuestions/loadAllPendingQuestions'),
                this.$store.dispatch('topics/loadAllTopics')
            ])
        },
        onReload() {
            this.isBusy = true

            this.loadData()
            .then(() => {
                this.isBusy = false
            })
        }
    }
}
</script>

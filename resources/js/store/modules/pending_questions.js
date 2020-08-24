const state = () => ({
    items: [],
    states: [],
    preloaded: {}
})

const getters = {
    totalCount: state => {
        return state.items.length
    },
    forLanguage: state => language => {
        // TODO Handle special case for English
        return state.items.filter(item => item.descriptions.hasOwnProperty(language))
    },
    forReview: state => {
        return state.items.filter(item => item.status.value !== 'pending')
    }
}

const actions = {
    loadAllPendingQuestions({ commit }) {
        return axios.get('/pending_questions')
        .then(response => {
            commit('setPendingQuestions', response.data.data)
        })
        .catch(error => {
            console.error('Pending questions loading:', error)
        })
    },
    preloadAllPendingQuestions({ state, dispatch, commit }) {
        if (!state.preloaded.hasOwnProperty('items')) {
            commit('setPreloaded', 'items')
            dispatch('loadAllPendingQuestions')
        }
    },
    updatePendingQuestion({ commit }, question) {
        commit('updatePendingQuestion', question)
    },
    loadStates({ commit }) {
        return axios.get('/pending_questions/states')
        .then(response => {
            commit('setStates', response.data)
        })
        .catch(error => {
            console.error('Pending question states:', error)
        })
    },
    preloadStates({ state, dispatch, commit }) {
        if (!state.preloaded.hasOwnProperty('states')) {
            commit('setPreloaded', 'states')
            dispatch('loadStates')
        }
    },
}

const mutations = {
    setPendingQuestions (state, questions) {
        state.items = questions
    },
    updatePendingQuestion (state, question) {
        const index = state.items.findIndex(item => {
            return item.id === question.id
        })

        if (index !== -1) {
            Vue.set(state.items, index, question)
        }
    },
    setPreloaded (state, value) {
        Vue.set(state.preloaded, value, value)
    },
    setStates (state, states) {
        state.states = states
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
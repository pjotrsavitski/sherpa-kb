const state = () => ({
    items: [],
    states: [],
    preloaded: []
})

const getters = {
    totalCount: state => {
        return state.items.length
    },
    forReview: state => {
        return state.items.filter(item => item.status.value !== 'in_translation')
    },
    published: state => {
        return state.items.filter(item => item.status.value === 'published')
    },
    forQuestion: state => {
        return state.items.filter(item => item.status.value === 'published' || item.status.value === 'translated')
    }
}

const actions = {
    loadAllAnswers({ commit }) {
        return axios.get('/answers')
        .then(response => {
            commit('setAnswers', response.data.data)
        })
        .catch(error => {
            console.error('Answers loading:', error)
        })
    },
    preloadAllAnswers({ state, dispatch, commit }) {
        if (!state.preloaded.hasOwnProperty('items')) {
            commit('setPreloaded', 'items')
            dispatch('loadAllAnswers')
        }
    },
    loadStates({ commit } ) {
        return axios.get('/answers/states')
        .then(response => {
            commit('setStates', response.data)
        })
        .catch(error => {
            console.error('Answer states loading:', error)
        })
    },
    preloadStates({ state, dispatch, commit }) {
        if (!state.preloaded.hasOwnProperty('states')) {
            commit('setPreloaded', 'states')
            dispatch('loadStates')
        }
    },
    insertAnswer({ commit }, answer) {
        commit('insertAnswer', answer)
    },
    updateAnswer({ commit }, answer) {
        commit('updateAnswer', answer)
    },
    deleteAnswer({ commit }, answer) {
        return axios.delete(`/answers/${answer.id}`)
            .then(response => {
                commit('deleteAnswer', response.data)
                // Notify questions store about answer removal
                this.dispatch('questions/answerDeleted', response.data)
            })
    }
}

const mutations = {
    setAnswers (state, answers) {
        state.items = answers
    },
    updateAnswer (state, answer) {
        const index = state.items.findIndex(item => {
            return item.id === answer.id
        })

        if (index !== -1) {
            Vue.set(state.items, index, answer)
        } else {
            console.warn('Index not found, could not update an answer:', answer)
        }
    },
    setPreloaded (state, value) {
        Vue.set(state.preloaded, value, value)
    },
    setStates(state, states) {
        state.states = states
    },
    insertAnswer(state, answer) {
        state.items.push(answer)
    },
    deleteAnswer(state, answer) {
        const index = state.items.findIndex(item => {
            return item.id === answer.id
        })

        if (index !== -1) {
            state.items.splice(index, 1)
        }
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}

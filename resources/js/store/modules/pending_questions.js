const state = () => ({
    items: []
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
    getAllPendingQuestions({ commit }) {
        axios.get('/pending_questions')
        .then(response => {
            commit('setPendingQuestions', response.data.data)
        })
        .catch(error => {
            console.error('Pending questions loading:', error);
        });
    },
    updatePendingQuestion({ commit }, question) {
        commit('updatePendingQuestion', question)
    }
}

const mutations = {
    setPendingQuestions (state, questions) {
        state.items = questions
    },
    updatePendingQuestion (state, question) {
        const index = state.items.findIndex(item => {
            return item.id === question.id
        });

        if (index !== -1) {
            Vue.set(state.items, index, question)
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
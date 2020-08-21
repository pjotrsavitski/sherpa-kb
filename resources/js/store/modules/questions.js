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
        return state.items.filter(item => item.status.value !== 'in_translation')
    }
}

const actions = {
    getAllQuestions({ commit }) {
        axios.get('/questions')
        .then(response => {
            commit('setQuestions', response.data.data)
        })
        .catch(error => {
            console.error('Questions loading:', error);
        });
    }
}

const mutations = {
    setQuestions (state, questions) {
        state.items = questions
    },
    updateQuestion (state, question) {
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
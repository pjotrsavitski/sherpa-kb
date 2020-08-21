const state = () => ({
    items: []
})

const getters = {
    totalCount: state => {
        return state.items.length
    }
}

const actions = {}

const mutations = {}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
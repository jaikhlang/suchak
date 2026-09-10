import states from './states'
import institutions from './institutions'
import sources from './sources'
import notices from './notices'
import moderation from './moderation'
const admin = {
    states: Object.assign(states, states),
institutions: Object.assign(institutions, institutions),
sources: Object.assign(sources, sources),
notices: Object.assign(notices, notices),
moderation: Object.assign(moderation, moderation),
}

export default admin
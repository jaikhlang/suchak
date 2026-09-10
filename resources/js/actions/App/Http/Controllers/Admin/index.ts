import DashboardController from './DashboardController'
import StateController from './StateController'
import InstitutionController from './InstitutionController'
import SourceController from './SourceController'
import NoticeManagementController from './NoticeManagementController'
import ModerationController from './ModerationController'
const Admin = {
    DashboardController: Object.assign(DashboardController, DashboardController),
StateController: Object.assign(StateController, StateController),
InstitutionController: Object.assign(InstitutionController, InstitutionController),
SourceController: Object.assign(SourceController, SourceController),
NoticeManagementController: Object.assign(NoticeManagementController, NoticeManagementController),
ModerationController: Object.assign(ModerationController, ModerationController),
}

export default Admin
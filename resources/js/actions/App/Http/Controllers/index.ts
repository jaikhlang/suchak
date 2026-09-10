import RecruitmentController from './RecruitmentController'
import SubscriptionController from './SubscriptionController'
import SitemapController from './SitemapController'
import FeedController from './FeedController'
import Admin from './Admin'
import Settings from './Settings'
const Controllers = {
    RecruitmentController: Object.assign(RecruitmentController, RecruitmentController),
SubscriptionController: Object.assign(SubscriptionController, SubscriptionController),
SitemapController: Object.assign(SitemapController, SitemapController),
FeedController: Object.assign(FeedController, FeedController),
Admin: Object.assign(Admin, Admin),
Settings: Object.assign(Settings, Settings),
}

export default Controllers
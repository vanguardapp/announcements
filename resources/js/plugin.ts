import '../css/plugin.css';

import NavbarBell from './components/NavbarBell.vue';
import Form from './pages/Form.vue';
import Index from './pages/Index.vue';
import List from './pages/List.vue';
import Show from './pages/Show.vue';

/**
 * Register everything this plugin contributes to the host.
 *
 * Names are namespaced with the plugin slug and must match what the PHP side
 * returns from Slot::component() and Inertia::render().
 */
window.Vanguard.plugin('announcements', (vanguard) => {
    vanguard.page('announcements::Index', Index);
    vanguard.page('announcements::List', List);
    vanguard.page('announcements::Show', Show);
    vanguard.page('announcements::Form', Form);
    vanguard.slot('announcements::NavbarBell', NavbarBell);
});

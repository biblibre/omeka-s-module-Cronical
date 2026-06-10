Cypress.Screenshot.defaults({
    capture: 'viewport',
})

Cypress.Commands.add('loginAsAdmin', () => {
    cy.env(['adminEmail', 'adminPassword']).then(env => {
        cy.visit('/login')
        cy.get('input[name="email"]').type(env.adminEmail);
        cy.get('input[name="password"]').type(env.adminPassword);
        cy.get('#loginform input[type="submit"]').click();
    });
});
Cypress.Commands.add('logout', () => {
    cy.visit('/logout');
});

describe('screenshots', () => {
    let lang;
    let strings;

    function __(key) {
        return strings[key]?.[lang] ?? strings[key]?.[''] ?? key;
    }

    before(function() {
        cy.loginAsAdmin();
        lang = Cypress.expose('omekaLang');
        cy.fixture('strings').then(_strings => { strings = _strings });
        cy.logout();
    });

    it('browse scheduled actions', () => {
        cy.loginAsAdmin();

        cy.visit('/admin/cronical/scheduled-action');
        cy.screenshot('images/scheduled-action-browse-empty');

        cy.visit('/admin/cronical/scheduled-action/add');
        cy.get('[name="o:action"]').select("Cronical\\Action\\Heartbeat", { force: true });
        cy.screenshot('images/scheduled-action-add-form');

        cy.get('#page-actions button').click();
        cy.get('[name="o:name"]').type('{selectall}{del}' + __('scheduledActionName'));
        cy.get('[type="checkbox"][name="o:is_active"]').check()
        cy.screenshot('images/scheduled-action-edit-form');

        cy.get('#page-actions button').click();
        cy.screenshot('images/scheduled-action-browse');
    })
})

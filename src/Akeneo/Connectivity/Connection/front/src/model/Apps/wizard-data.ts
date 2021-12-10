import ScopeMessage from './scope-message';

export interface AppWizardData {
    appName: string;
    appLogo: string;
    scopeMessages: ScopeMessage[];
    authenticationScopes: Array<'openid' | 'email' | 'profile'>;
}

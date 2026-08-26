import { ref, computed } from 'vue';
import th from './th';
import en from './en';

const messages = {
    th,
    en,
};

const STORAGE_KEY = 'mini_lms_locale';

// Initialize from localStorage if exists, default to 'th'
const savedLocale = typeof window !== 'undefined' ? localStorage.getItem(STORAGE_KEY) : null;
const initialLocale = savedLocale && messages[savedLocale] ? savedLocale : 'th';

export const currentLocale = ref(initialLocale);

// Set HTML document lang attribute
if (typeof document !== 'undefined') {
    document.documentElement.lang = currentLocale.value;
}

/**
 * Set the current active language and persist to localStorage
 * @param {'th'|'en'} lang 
 */
export function setLocale(lang) {
    if (messages[lang]) {
        currentLocale.value = lang;
        if (typeof window !== 'undefined') {
            localStorage.setItem(STORAGE_KEY, lang);
        }
        if (typeof document !== 'undefined') {
            document.documentElement.lang = lang;
        }
    }
}

/**
 * Resolve a nested key from an object (e.g. "profile.personal_info.title")
 */
function getNestedTranslation(obj, path) {
    if (!obj || !path) return null;
    const keys = path.split('.');
    let current = obj;
    for (const key of keys) {
        if (current && typeof current === 'object' && key in current) {
            current = current[key];
        } else {
            return null;
        }
    }
    return typeof current === 'string' ? current : null;
}

/**
 * Translate a key into the current active language with parameter interpolation
 * @param {string} key - e.g. "profile.title" or "nav.courses"
 * @param {Object} [params] - e.g. { lang: 'ภาษาไทย' }
 * @returns {string}
 */
export function t(key, params = {}) {
    // 1. Try current locale
    let text = getNestedTranslation(messages[currentLocale.value], key);

    // 2. Fallback to English
    if (!text && currentLocale.value !== 'en') {
        text = getNestedTranslation(messages.en, key);
    }

    // 3. Fallback to key itself
    if (!text) {
        text = key;
    }

    // 4. Interpolate params (:param or {param})
    if (params && typeof params === 'object') {
        for (const [pKey, pVal] of Object.entries(params)) {
            text = text.replace(new RegExp(`:${pKey}`, 'g'), String(pVal));
            text = text.replace(new RegExp(`\\{${pKey}\\}`, 'g'), String(pVal));
        }
    }

    return text;
}

/**
 * Composable for use in Vue components
 */
export function useI18n() {
    return {
        locale: currentLocale,
        setLocale,
        t,
        availableLocales: [
            { code: 'th', name: 'ภาษาไทย', label: 'TH' },
            { code: 'en', name: 'English', label: 'EN' },
        ],
    };
}

/**
 * Vue Plugin installer
 */
export default {
    install(app) {
        app.config.globalProperties.$t = t;
        app.config.globalProperties.$locale = currentLocale;
        app.config.globalProperties.$setLocale = setLocale;
        app.provide('i18n', {
            locale: currentLocale,
            setLocale,
            t,
        });
    },
};

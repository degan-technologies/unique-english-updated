/**
 * useIdleTimeout.js
 *
 * Vue 3 composable that logs out the user after a configurable period of
 * inactivity (mouse move, keyboard, click, scroll, touch).
 *
 * Usage — mount once in the root App component:
 *
 *   import { useIdleTimeout } from "@/composables/useIdleTimeout";
 *   useIdleTimeout();   // uses default 15-minute timeout
 *
 * How it works:
 *  1. When the user logs in, start listening for activity events.
 *  2. Any activity resets a debounced countdown timer.
 *  3. When the timer fires (no activity for IDLE_MS), call the logout flow:
 *     a. POST /api/logout  → revoke the Passport token on the server
 *     b. Clear frontend Pinia auth state
 *     c. Redirect to / (root, where the login modal is available)
 *  4. Stop all listeners when the user logs out or the component unmounts.
 */

import { useAppStore } from "@/store/useAppStore";
import { storeToRefs } from "pinia";
import { onMounted, onUnmounted, watch } from "vue";

/** Inactivity threshold in milliseconds (15 minutes) */
const IDLE_MS = 15 * 60 * 1000;

/** DOM events that count as user activity */
const ACTIVITY_EVENTS = [
    "mousemove",
    "mousedown",
    "keydown",
    "touchstart",
    "scroll",
    "click",
    "wheel",
];

export function useIdleTimeout(idleMs = IDLE_MS) {
    const appStore = useAppStore();
    const { isLoggedIn } = storeToRefs(appStore);

    let timer = null;

    // ─── Core helpers ────────────────────────────────────────────────────────

    function resetTimer() {
        clearTimeout(timer);
        timer = setTimeout(handleIdle, idleMs);
    }

    async function handleIdle() {
        if (!isLoggedIn.value) return; // User already logged out elsewhere

        console.info("[IdleTimeout] Session expired due to inactivity.");
        await appStore.forceLogout("Your session expired due to inactivity.");
    }

    // ─── Listener management ─────────────────────────────────────────────────

    function startListeners() {
        ACTIVITY_EVENTS.forEach((event) =>
            window.addEventListener(event, resetTimer, { passive: true }),
        );
        resetTimer(); // Start the initial countdown
    }

    function stopListeners() {
        clearTimeout(timer);
        ACTIVITY_EVENTS.forEach((event) =>
            window.removeEventListener(event, resetTimer),
        );
    }

    // ─── Lifecycle: attach / detach based on login state ─────────────────────

    onMounted(() => {
        if (isLoggedIn.value) {
            startListeners();
        }
    });

    onUnmounted(() => {
        stopListeners();
    });

    // React to login / logout events
    watch(isLoggedIn, (loggedIn) => {
        if (loggedIn) {
            startListeners();
        } else {
            stopListeners();
        }
    });

    // Expose for testing / manual control if needed
    return { resetTimer, stopListeners };
}

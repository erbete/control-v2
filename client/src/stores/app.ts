import { defineStore } from "pinia";
import type { ServiceIssue } from "@/services/types";
import type { ProblemDetail } from "@/models";
import { TimeoutError } from "@/services/fetchTools";

export enum SnackbarType {
    Success,
    Warning,
    Danger,
    Info
}

type Snackbar = {
    show: boolean,
    text: string,
    color: string,
}

type ProblemModal = {
    open: boolean,
    problem: ProblemDetail,
}

interface AppState {
    problemModal: ProblemModal,
    snackbar: Snackbar
}

export const useAppStore = defineStore("app", {
    state: (): AppState => {
        return {
            problemModal: {
                open: false,
                problem: {
                    type: "https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/418",
                    title: "Det har oppstått feil",
                    detail: "Noe gikk galt, vennligst prøv igjen.",
                    errors: [],
                }
            },
            snackbar: {
                show: false,
                text: "",
                color: ""
            },
        };
    },

    actions: {
        showSnackbar(text: string, type: SnackbarType, timeout = 4000) {
            if (type === SnackbarType.Success) this.snackbar.color = "var(--pico-primary)";
            if (type === SnackbarType.Warning) this.snackbar.color = "#ffa500";
            if (type === SnackbarType.Danger) this.snackbar.color = "var(--pico.del-color)";
            if (type === SnackbarType.Info) this.snackbar.color = "var(--pico-card-background-color)";

            this.snackbar.text = text;
            this.snackbar.show = true;

            setTimeout(() => {
                this.snackbar.show = false;
            }, timeout);
        },

        onProblemNotify(issue: ServiceIssue) {
            // Timeout and generic/default errors
            if (issue.status.isNothing && issue.problem.isJust) {
                const problem = issue.problem.value;

                // show snackbar for timeout errors
                if (problem instanceof TimeoutError) {
                    this.showSnackbar(problem.message, SnackbarType.Warning, 10000);
                    return;
                }

                // show problem modal if it's a generic/undefined error
                this.problemModal.problem = {
                    type: "https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/418",
                    title: "Det har oppstått feil",
                    detail: "Noe gikk galt, vennligst prøv igjen.",
                    errors: [],
                };

                this.problemModal.open = true;
            }

            if (issue.status.isJust && issue.problem.isJust) {
                const status = issue.status.value;

                // Page expired (CSRF token mismatch)
                if (status === 419) {
                    this.problemModal.problem = {
                        type: "https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/418",
                        title: "Det har oppstått feil",
                        detail: "Nettsiden har utløpt, oppdater siden eller kontakt IT for hjelp.",
                        errors: [],
                    };

                    this.problemModal.open = true;

                    return;
                }

                // Validation errors
                const problem = issue.problem.value as ProblemDetail;
                const { type, title, detail, errors } = problem;
                const errs: any[] = [];
                if (errors.length > 0) {
                    for (const error of errors) {
                        for (const err in error) {
                            errs.push(...error[err]);
                        }
                    }
                }

                this.problemModal.problem = {
                    type: type,
                    title: title,
                    detail: detail,
                    errors: errs
                };

                this.problemModal.open = true;
            }
        },
    }
});

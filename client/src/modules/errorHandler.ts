import type { ServiceIssue } from "@/services/types";

export default function useErrorHandler() {
    const handleError = (issue: ServiceIssue) => {
        console.log("From Error Handler Module", issue.problem.unwrapOr("Something went wrong"));
    };

    return {
        handleError
    };
}

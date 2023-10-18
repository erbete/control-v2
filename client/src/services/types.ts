import type { Maybe, Result } from "true-myth";

export type ServicePayload<T> = {
    status: number;
    data: T;
};

export type ServiceIssue = {
    status: Maybe<number>,
    problem: Maybe<any>,
};

export type ServiceResult<T> = Result<Readonly<ServicePayload<T>>, ServiceIssue>;

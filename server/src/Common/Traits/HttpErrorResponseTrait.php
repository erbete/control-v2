<?php

namespace Control\Common\Traits;

use Illuminate\Http\Response;

trait HttpErrorResponseTrait
{
    const ERROR_TYPE_URLS = [
        Response::HTTP_INTERNAL_SERVER_ERROR => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/500',
        Response::HTTP_NOT_FOUND => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/404',
        Response::HTTP_UNPROCESSABLE_ENTITY => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/422',
        Response::HTTP_FORBIDDEN => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/403',
        Response::HTTP_UNAUTHORIZED => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/401',
        Response::HTTP_CONFLICT => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/409',
    ];

    const DETAIL_TITLES = [
        Response::HTTP_INTERNAL_SERVER_ERROR => 'Serveren kan ikke fullføre forespørselen. Vennligst prøv igjen senere.',
        Response::HTTP_NOT_FOUND => 'Den forespurte ressursen kan ikke bli funnet.',
        Response::HTTP_UNPROCESSABLE_ENTITY => 'Feilaktig utfylt data. Vennligst sjekk og prøv igjen.',
        Response::HTTP_FORBIDDEN => 'Tilgang forbudt.',
        Response::HTTP_UNAUTHORIZED => 'Tilgang nektet. Logg inn eller sjekk tilgangsrettigheter.',
        Response::HTTP_CONFLICT => 'Dataen har blitt oppdatert av en annen bruker. Vennligst prøv igjen.',
        'default' => 'Serveren kan ikke fullføre forespørselen. Vennligst prøv igjen senere.',
    ];

    public function responseFailure(int $status, string $detail = '', array $errors = [])
    {
        $typeUrl = self::ERROR_TYPE_URLS[$status] ?? 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/418';
        $detail = empty($detail) ? (self::DETAIL_TITLES[$status] ?? self::DETAIL_TITLES['default']) : $detail;

        return response()->json(
            new ProblemDetail(
                type: $typeUrl,
                title: $this->getDetailTitle($status),
                detail: $detail,
                errors: $errors
            ),
            $status
        );
    }

    private function getDetailTitle(int $status): string
    {
        return match ($status) {
            Response::HTTP_INTERNAL_SERVER_ERROR => 'Intern serverfeil',
            Response::HTTP_NOT_FOUND => 'Ressurs ikke funnet',
            Response::HTTP_UNPROCESSABLE_ENTITY => 'Ugyldig data',
            Response::HTTP_FORBIDDEN => 'Forbudt',
            Response::HTTP_UNAUTHORIZED => 'Uautorisert tilgang',
            Response::HTTP_CONFLICT => 'Oppdateringskonflikt',
            default => 'Noe gikk galt',
        };
    }
}

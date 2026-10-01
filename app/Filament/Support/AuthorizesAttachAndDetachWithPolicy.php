<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Illuminate\Auth\Access\Response;

/**
 * Filament's relation managers only check isReadOnly() for attach and detach actions and
 * never consult a policy. This adds the related model's attach / detach / detachAny policy
 * checks on top, so the actions are hidden and refused server side for unauthorized users.
 */
trait AuthorizesAttachAndDetachWithPolicy
{
    public function getDefaultActionAuthorizationResponse(Action $action): ?Response
    {
        $response = parent::getDefaultActionAuthorizationResponse($action);

        if ($response?->denied()) {
            return $response;
        }

        return match (true) {
            $action instanceof AttachAction => $this->getAttachAuthorizationResponse(),
            $action instanceof DetachBulkAction => $this->getDetachAnyAuthorizationResponse(),
            $action instanceof DetachAction => $this->getDetachAuthorizationResponse($action->getRecord()),
            default => $response,
        };
    }
}

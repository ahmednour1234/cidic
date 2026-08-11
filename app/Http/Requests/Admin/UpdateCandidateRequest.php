<?php

namespace App\Http\Requests\Admin;

/**
 * Update shares the store rules; uploads stay optional so an edit that omits a
 * file keeps the existing one.
 */
class UpdateCandidateRequest extends StoreCandidateRequest
{
}

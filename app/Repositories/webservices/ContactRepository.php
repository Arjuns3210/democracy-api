<?php

namespace App\Repositories\webservices;

//Common
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Contact;

/**
 * Class ContactRepository
 *
 * @package App\Repositories\webservices
 * @version Sep 23, 2023
 */
class ContactRepository
{
    /**
     * Create Contact
     *
     * @param array $attributes
     */
    public function create(array $attributes)
    {
        try {
            \DB::beginTransaction();
            $contact = Contact::create($attributes);
            \DB::commit();
            return $contact;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e);
            throw $e;
        }
    }

}

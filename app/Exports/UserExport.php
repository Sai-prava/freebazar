<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::select(
            'user_id',
            'name',
            'email',
            'gender',
            'address',
            'mobilenumber',
            'city',
            'state',
            'country',
            'pan_no',
            'ifsc',
            'account_no',
            'nominee_name',
            'bank',
            'relation_user',
            'zip',
            'sponsor_id',
        )->get();
    }

    public function headings(): array
    {
        return [
            'User ID',       
            'Name',      
            'Email',       
            'Gender',      
            'Address',       
            'Mobile Number',  
            'City',           
            'State',        
            'Country',      
            'PAN Number',    
            'IFSC Code',      
            'Account Number', 
            'Nominee Name',   
            'Bank Name',      
            'Relation to User', 
            'ZIP Code',     
            'Sponsor ID',    
        ];
    }
}

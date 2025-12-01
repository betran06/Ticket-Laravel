<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TicketStoreRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;



class TicketController extends Controller
{
    public function store(TicketStoreRequest $request)
    {
        $data = $request->validated();
        
        DB::beginTransaction();

        try {
            $ticket = new ticket;
            $ticket->user_id = auth()->user()->id; //hanya user yang bisa membuat ticket untuk admin tidak bisa
            $ticket->code = 'TIC-'. rand(10000, 99999);
            $ticket->title = $data['title'];
            $ticket->description = $data['description'];
            $ticket->priority = $data['priority'];
            $ticket->save();

            DB::commit();

            return response()->json([
                'message' => 'Ticket berhasil ditambahkan',
                'data' => new TicketResource($ticket)
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Terjadi Kesalahan',
                'data' => null
            ], 500);
        }
    }
}

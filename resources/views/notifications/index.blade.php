@extends('layouts.app')

@section('title', 'Notifications')
@section('header', 'Notifications')

@section('content')
<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Notifications</h2>
            <p class="text-[13px] text-slate-400 mt-0.5">Historique de vos notifications</p>
        </div>
        @if($notifications->total() > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 text-[13px] font-medium text-slate-500 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Tout marquer comme lu
                </button>
            </form>
        @endif
    </div>

    {{-- Notifications list --}}
    <div class="space-y-0">
        @forelse($notifications as $notification)
        <div class="flex items-start gap-4 px-5 py-4 border-b border-slate-100 {{ $notification->isRead() ? 'bg-white' : 'bg-slate-50/70' }} hover:bg-slate-50/50 transition-colors">
            {{-- Icon --}}
            <div class="shrink-0 mt-0.5">
                @if($notification->type === 'vehicle_validated')
                    <div class="w-8 h-8 flex items-center justify-center bg-emerald-50">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                @elseif($notification->type === 'vehicle_rejected')
                    <div class="w-8 h-8 flex items-center justify-center bg-rose-50">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                @else
                    <div class="w-8 h-8 flex items-center justify-center bg-slate-100">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <p class="text-[13px] font-medium text-slate-900">{{ $notification->title }}</p>
                    @if(!$notification->isRead())
                        <span class="inline-block w-2 h-2 bg-blue-500 rounded-full shrink-0"></span>
                    @endif
                </div>
                <p class="text-[13px] text-slate-500 mt-0.5">{{ $notification->message }}</p>
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-[11px] text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                    @if($notification->data && isset($notification->data['vehicle_id']))
                        <a href="{{ route('vehicles.show', $notification->data['vehicle_id']) }}" class="text-[11px] text-slate-500 hover:text-slate-900 underline transition-colors">Voir le vehicule</a>
                    @endif
                </div>
            </div>

            {{-- Mark as read --}}
            @if(!$notification->isRead())
            <div class="shrink-0">
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" data-no-loading>
                    @csrf
                    <button type="submit" class="text-[11px] text-slate-400 hover:text-slate-700 transition-colors whitespace-nowrap" title="Marquer comme lu">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div class="px-5 py-16 text-center">
            <div class="flex flex-col items-center">
                <svg class="w-10 h-10 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                <p class="text-[13px] font-medium text-slate-900">Aucune notification</p>
                <p class="text-[12px] text-slate-400 mt-1">Vous n'avez aucune notification pour le moment</p>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div class="px-5 py-4 border-t border-slate-200">
        {{ $notifications->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection

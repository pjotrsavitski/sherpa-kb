@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    <ul class="nav nav-pills nav-fill">
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                Questions
                                <span class="badge badge-secondary badge-pill">13</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                Pending questions
                                <span class="badge badge-secondary badge-pill">9</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                Answers
                                <span class="badge badge-secondary badge-pill">11</span>
                            </a>
                        </li>
                    </ul>

                    <h2>
                        {{ __('Pending questions') }}
                    </h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">
                                    {{ __('Description') }}
                                </th>
                                <th scope="col">
                                    {{ __('English translation') }}
                                </th>
                                <th scope="col">
                                    {{ __('Group') }}
                                </th>
                                <th scope="col">
                                    {{ __('Date') }}
                                </th>
                                <th scope="col">
                                    {{ __('Status') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingQuestions as $question)
                            <tr scope="row">
                                <td>
                                    {{ $question->id }}
                                </td>
                                <td>
                                    {{ $question->getDescription() }}
                                </td>
                                <td>
                                    {{ $question->getEnglishDescription() }}
                                </td>
                                <td>
                                    {{ $question->group_no }}
                                </td>
                                <td>
                                    {{ $question->created_at->format('d.m.Y') }}
                                </td>
                                <td>
                                    {{ $question->status->status() }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    {{ __('No pending questions') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



@extends('web.layouts.app')
@section('title')
    Отчет "Анализ повторяемости несоответствий"
@endsection

@section('content')
    @include('web.include.sidebar_doc')
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"><h2 class="text-muted" style="text-align: center" >Отчет "Анализ повторяемости несоответствий" по состоянию на по</h2>
                        @can('role-create')
                            <div class="bat_info"><a href="{{ url('pdf_opo') }}">Создать PDF</a></div>
                        @endcan
                    </div>


                    <div style="background: #FFFFFF; border-radius: 6px" class="inside_tab_padding form51">
                        <div  style="" class="row_block form51">

                <table>
                    <thead>
                    <tr>
                        <th rowspan="2" class="centered">Наименование ОПО</th>
                        <th rowspan="2" class="centered">Элемент ОПО</th>
                        <th rowspan="2" class="centered">Наименование повторного несоответствия (требования законодательства)</th>
                        <th colspan="4" class="centered">Выявлено при проведении контрольных мероприятий</th>
                        <th rowspan="2" class="centered">Всего за период</th>
                        <th rowspan="2" class="centered">% устранения</th>
                        <th rowspan="2" class="centered">% от общего количества выявленых</th>
                    </tr>
                    <tr>
                        <th class="centered">I уровень</th>
                        <th class="centered">II уровень</th>
                        <th class="centered">III уровень</th>
                        <th class="centered">IV уровень</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($rows5 as $row)
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
{{--                            <td>{{$row->id}}</td>--}}
{{--                            <td>{{$row->id}}</td>--}}
{{--                            <td>{{$row->id}}</td>--}}
{{--                            <td>{{$row->id.''.$row->id}}</td>--}}
{{--                            <td>{{$row->id.''.$row->id.''.$row->id}}</td>--}}
{{--                            <td>{{$row->id.''.$row->id.''.$row->id.''.$row->id}}</td>--}}
{{--                            <td>{{$row->id.''.$row->id.''.$row->id.''.$row->id.''.$row->id}}</td>--}}
{{--                            <td>{{$row->id}}</td>--}}
{{--                            <td>{{$row->id}}</td>--}}
{{--                            <td>{{$row->id}}</td>--}}
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>



@endsection

<div class="numbs_line centered">


    @foreach ($all_opo as $this_opo)

        <div class="numbs_single
       @if ($this_opo->opo_to_calc1->first()->status == '1') good
                                  @elseif ($this_opo->opo_to_calc1->first()->status == '2') normal
                                  @elseif ($this_opo->opo_to_calc1->first()->status == '3') critical
                                @else bad
                                @endif

                "><a href="/opo/{{$this_opo->idOPO}}/main">{{$this_opo->descOPO}}
                <span>{{$this_opo->opo_to_calc1->first()->ip_opo}}</span></a></div>

    @endforeach

</div>

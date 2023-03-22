import { Component, OnInit } from '@angular/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { CalendarOptions } from '@fullcalendar/core';
import { ChangeDetectorRef } from '@angular/core';
import { FullCalendarModule } from '@fullcalendar/angular';

@Component({
  selector: 'app-programme',
  templateUrl: './programme.component.html',
  styleUrls: ['./programme.component.scss'],
})

export class ProgrammeComponent implements OnInit {
  events = [
    { title: 'module 1', date: '2023-01-02' },
    { title: 'module 2', date: '2023-01-17' },
    { title: 'module 3', date: '2023-01-11' },
    { title: 'module 4', date: '2023-01-25' },
  ];
  calendarOptions: CalendarOptions = {
    initialView: 'dayGridMonth',
    plugins: [interactionPlugin, dayGridPlugin],
    events: this.events,
  };
  constructor() {}
  ngOnInit() {
    // setTimeout(() => {
    this.calendarOptions.dateClick = this.onDateClick.bind(this);
    // }, 3500)
    // this.cdr.detectChanges();
  }

  onDateClick(res: { dateStr: string }) {
    //alert("You clicked on " + res.dateStr);
    var userInput = prompt('Entrez nom du module :');
    if (userInput !== null) {
      var newEvent = { title: userInput, date: res.dateStr };
      this.events.push(newEvent);
      this.calendarOptions.events = [...this.events];
    }
    console.log('Events : ', this.events);
    console.log('Events Calendar : ', this.calendarOptions.events);
  }
}

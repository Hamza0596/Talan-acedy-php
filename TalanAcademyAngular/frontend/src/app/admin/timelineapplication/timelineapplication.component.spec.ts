import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TimelineapplicationComponent } from './timelineapplication.component';

describe('TimelineapplicationComponent', () => {
  let component: TimelineapplicationComponent;
  let fixture: ComponentFixture<TimelineapplicationComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ TimelineapplicationComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(TimelineapplicationComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

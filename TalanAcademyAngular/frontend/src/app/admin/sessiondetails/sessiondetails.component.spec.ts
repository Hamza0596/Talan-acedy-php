import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SessiondetailsComponent } from './sessiondetails.component';

describe('SessiondetailsComponent', () => {
  let component: SessiondetailsComponent;
  let fixture: ComponentFixture<SessiondetailsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ SessiondetailsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SessiondetailsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

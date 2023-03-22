import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ApprentiComponent } from './apprenti.component';

describe('ApprentiComponent', () => {
  let component: ApprentiComponent;
  let fixture: ComponentFixture<ApprentiComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ApprentiComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(ApprentiComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CorrectionsComponent } from './corrections.component';

describe('CorrectionsComponent', () => {
  let component: CorrectionsComponent;
  let fixture: ComponentFixture<CorrectionsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CorrectionsComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CorrectionsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

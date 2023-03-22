import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LessonAdminComponent } from './lesson-admin.component';

describe('LessonAdminComponent', () => {
  let component: LessonAdminComponent;
  let fixture: ComponentFixture<LessonAdminComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ LessonAdminComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LessonAdminComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CursusAdminComponent } from './cursus-admin.component';

describe('CursusAdminComponent', () => {
  let component: CursusAdminComponent;
  let fixture: ComponentFixture<CursusAdminComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ CursusAdminComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CursusAdminComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

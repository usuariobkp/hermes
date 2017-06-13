package models

import (
	"ratings/controller"
	"testing"

	"github.com/dchest/uniuri"
	"github.com/stretchr/testify/require"
)

func TestCreateBrand(t *testing.T) {
	db := controller.GetWriteDB()
	defer db.Close()

	name := uniuri.New()
	brand := Brand{Name: name}
	result := db.Create(&brand)

	require.Equal(t, nil, result.Error)

	if value, ok := result.Value.(*Brand); ok {
		require.Equal(t, name, value.Name)
	} else {
		t.Fatal("Value is not a Brand")
	}
}

func TestGetBrand(t *testing.T) {
	writeDb := controller.GetWriteDB()
	defer writeDb.Close()
	readDb := controller.GetReadDB()
	defer readDb.Close()

	name := uniuri.New()
	brand := Brand{Name: name}
	record := writeDb.Create(&brand)

	if value, ok := record.Value.(*Brand); ok {
		var result Brand

		readDb.First(&result, value.ID)
		require.Equal(t, name, result.Name)
	} else {
		t.Fatal("Value is not a Brand")
	}
}
